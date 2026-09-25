# Events

Polycart fires two families of events:

- **Model events:** every Eloquent lifecycle hook of every Polycart model, one class per hook.
- **Action events:** a start event and a finish event for every action. Every operation on a cart is an action, whether it runs through code, the JSON API, MCP, Cortex or the dashboard.

Every event carries the models involved, not just their ids. Every event also uses `Dispatchable` and `SerializesModels`, so it can be dispatched with `::dispatch()` and handled by queued listeners.

This guide covers:

- [Model events](#model-events)
- [Action events](#action-events)
- [Listening to a whole family](#listening-to-a-whole-family)
- [Timing and transactions](#timing-and-transactions)
- [Every action and its events](#every-action-and-its-events)
- [Testing](#testing)

## Model events

Each model fires a class-based event for every Eloquent hook that applies to it:

| Model | Events |
| --- | --- |
| `Cart` | all 15: `retrieved`, `creating`, `created`, `updating`, `updated`, `saving`, `saved`, `deleting`, `deleted`, `restoring`, `restored`, `trashed`, `forceDeleting`, `forceDeleted`, `replicating` |
| `CartLine`, `CartMember`, `CartPath`, `CartActivity` | the 10 that apply to models without soft deletes: `retrieved`, `creating`, `created`, `updating`, `updated`, `saving`, `saved`, `deleting`, `deleted`, `replicating` |

They live in `JayI\Polycart\Events\Model` and are named `{Model}{Hook}Event`, for example `CartCreatingEvent` or `CartLineDeletedEvent`. The model is a typed property: `$event->cart`, `$event->line`, `$event->member`, `$event->path`, `$event->activity`. It is also available as `$event->model()`, alongside `$event->hook()`.

```php
use JayI\Polycart\Events\Model\CartLineSavingEvent;

Event::listen(CartLineSavingEvent::class, function (CartLineSavingEvent $event) {
    $event->line->meta = [...$event->line->meta, 'checked_at' => now()->toIso8601String()];
});
```

About how they fire:

- **Synchronous:** they fire when Eloquent fires the hook, as Eloquent's own events do.
- **Cancelling:** a `creating`, `updating`, `saving`, `deleting`, `restoring` or `forceDeleting` listener that returns `false` stops the operation.
- **Subclasses:** a cart that hydrates as a subclass, such as your `Quote extends Cart`, fires the `Cart*` events. A listener sees every cart, whatever its class.
- **Your own mapping:** entries you declare on a subclass's `$dispatchesEvents` win over the derived ones.

The mapping is done by the `DispatchesModelEvents` trait (`JayI\Polycart\Models\Concerns`). It is the same convention the rest of your application may already use.

## Action events

Every action dispatches two events:

1. **A start event** (`…ingActionEvent`, e.g. `LineAddingActionEvent`), before the action does any work. It carries the action's input.
2. **A finish event** (`…edActionEvent`, e.g. `LineAddedActionEvent`), once the action has succeeded. It carries the result.

```php
use JayI\Polycart\Events\Action\CartConvertedActionEvent;
use JayI\Polycart\Events\Action\LineAddingActionEvent;

Event::listen(LineAddingActionEvent::class, function (LineAddingActionEvent $event) {
    Log::info('Adding to cart', ['cart' => $event->cart->id, 'quantity' => $event->quantity]);
});

Event::listen(CartConvertedActionEvent::class, function (CartConvertedActionEvent $event) {
    if ($event->to === 'order') {
        SendOrderToErp::dispatch($event->cart);
    }
});
```

- **Failure:** an action that throws fires its start event and no finish event.
- **Nesting:** actions that call other actions fire both sets. Adding a batch fires `LinesAddingActionEvent`, then a `LineAdding`/`LineAdded` pair for each line, then `LinesAddedActionEvent`.

Action events live in `JayI\Polycart\Events\Action`.

## Listening to a whole family

Each family implements an interface in `JayI\Polycart\Contracts`, and Laravel delivers an event to listeners of the interfaces it implements:

| Interface | Receives |
| --- | --- |
| `ModelLifecycleEvent` | every model event |
| `ActionStartingEvent` | every action start |
| `ActionFinishedEvent` | every action finish |

```php
use JayI\Polycart\Contracts\ActionFinishedEvent;

Event::listen(ActionFinishedEvent::class, fn (ActionFinishedEvent $event) => Metrics::increment(class_basename($event)));
```

## Timing and transactions

| Family | Dispatched |
| --- | --- |
| Model events | Immediately, as Eloquent fires the hook |
| Action start events | Immediately, before the action touches the database |
| Action finish events | After the surrounding transaction commits (`ShouldDispatchAfterCommit`), and not at all if it rolls back |

Because finish events wait for the commit, listeners never hear about work that was undone. That covers:

- a batch that was refused part-way
- a conversion that failed validation
- your own `DB::transaction()` that threw after calling Polycart

Outside a transaction, finish events fire straight away.

## Every action and its events

| Action | Start event (carries) | Finish event (carries) |
| --- | --- | --- |
| `ActiveCartAction` | `ActiveCartResolvingActionEvent` (`type`, `owner`, `scope`) | `ActiveCartResolvedActionEvent` (`cart`) |
| `AddLineAction` | `LineAddingActionEvent` (`cart`, `purchasable`, `quantity`, `options`, `meta`, `unitPrice`) | `LineAddedActionEvent` (`cart`, `line`, `merged`) |
| `AddLinesAction` | `LinesAddingActionEvent` (`cart`, `lines`) | `LinesAddedActionEvent` (`cart`, `lines`) |
| `ClearCartAction` | `CartClearingActionEvent` (`cart`) | `CartClearedActionEvent` (`cart`) |
| `ConvertCartAction` | `CartConvertingActionEvent` (`cart`, `to`, `copy`) | `CartConvertedActionEvent` (`source`, `cart`, `from`, `to`, `copy`) |
| `CreateCartAction` | `CartCreatingActionEvent` (`type`, `owner`, `attributes`, `scope`) | `CartCreatedActionEvent` (`cart`) |
| `DeleteCartAction` | `CartDeletingActionEvent` (`cart`) | `CartDeletedActionEvent` (`cart`) |
| `ListActivityAction` | `ActivityListingActionEvent` (`cart`, `filters`) | `ActivityListedActionEvent` (`cart`, `activity`) |
| `ListCartTypesAction` | `CartTypesListingActionEvent` (none) | `CartTypesListedActionEvent` (`types`) |
| `ListCartsAction` | `CartsListingActionEvent` (`filters`, `viewer`) | `CartsListedActionEvent` (`carts`, `viewer`) |
| `ListMembersAction` | `MembersListingActionEvent` (`cart`) | `MembersListedActionEvent` (`cart`, `members`) |
| `MergeCartsAction` | `CartsMergingActionEvent` (`from`, `into`) | `CartsMergedActionEvent` (`from`, `into`) |
| `RemoveLineAction` | `LineRemovingActionEvent` (`cart`, `line`) | `LineRemovedActionEvent` (`cart`, `lineId`) |
| `RemoveLinesAction` | `LinesRemovingActionEvent` (`cart`, `lines`) | `LinesRemovedActionEvent` (`cart`, `lineIds`) |
| `SetVisibilityAction` | `VisibilityChangingActionEvent` (`cart`, `to`) | `VisibilityChangedActionEvent` (`cart`, `from`, `to`) |
| `ShareCartAction` | `CartSharingActionEvent` (`cart`, `member`, `role`) | `CartSharedActionEvent` (`cart`, `member`) |
| `ShowCartAction` | `CartShowingActionEvent` (`cart`) | `CartShownActionEvent` (`cart`) |
| `TransitionCartAction` | `CartTransitioningActionEvent` (`cart`, `to`) | `CartTransitionedActionEvent` (`cart`, `from`, `to`) |
| `UnshareCartAction` | `CartUnsharingActionEvent` (`cart`, `member`) | `CartUnsharedActionEvent` (`cart`, `memberType`, `memberId`) |
| `UpdateCartAction` | `CartUpdatingActionEvent` (`cart`, `data`) | `CartUpdatedActionEvent` (`cart`, `changed`) |
| `UpdateLineAction` | `LineUpdatingActionEvent` (`cart`, `line`, `quantity`, `unitPrice`) | `LineUpdatedActionEvent` (`cart`, `line`) |
| `UpdateLinesAction` | `LinesUpdatingActionEvent` (`cart`, `changes`) | `LinesUpdatedActionEvent` (`cart`) |

Notes on particular events:

- **`LineAddedActionEvent`:** `merged` is true when the add went into a matching line.
- **`LineUpdatedActionEvent`:** `line` is `null` when a quantity of zero removed the line.
- **`LineRemovedActionEvent`, `LinesRemovedActionEvent`, `CartUnsharedActionEvent`:** these carry ids, because the row is gone.
- **`CartConvertedActionEvent`:** `source` is the original cart. When the cart was retyped in place, `source` and `cart` are the same row.

## Testing

```php
use JayI\Polycart\Events\Action\LineAddedActionEvent;

Event::fake([LineAddedActionEvent::class]);

$cart->add($product, 2);

Event::assertDispatched(LineAddedActionEvent::class, fn ($event) => $event->line->quantity === 2 && ! $event->merged);
```

Fake only the events you assert on. A bare `Event::fake()` also stops the model hooks a cart needs when it is created: its initial status, expiry, place in the tree, the creator's membership and the first activity entry.
