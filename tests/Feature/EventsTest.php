<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use JayI\Polycart\Actions\DeleteCartAction;
use JayI\Polycart\Actions\ListActivityAction;
use JayI\Polycart\Actions\ListCartsAction;
use JayI\Polycart\Actions\ListCartTypesAction;
use JayI\Polycart\Actions\ListMembersAction;
use JayI\Polycart\Actions\ShowCartAction;
use JayI\Polycart\Actions\UpdateCartAction;
use JayI\Polycart\Contracts\ActionFinishedEvent;
use JayI\Polycart\Contracts\ActionStartingEvent;
use JayI\Polycart\Contracts\ModelLifecycleEvent;
use JayI\Polycart\Enums\Visibility;
use JayI\Polycart\Events\Action\LineAddingActionEvent;
use JayI\Polycart\Events\Action\LinesAddedActionEvent;
use JayI\Polycart\Events\Action\LinesAddingActionEvent;
use JayI\Polycart\Events\Model\CartCreatingEvent;
use JayI\Polycart\Exceptions\LineRejectedException;
use JayI\Polycart\Facades\Polycart;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartLine;
use JayI\Polycart\Tests\Fixtures\Models\Quote;
use JayI\Polycart\Tests\Fixtures\Types\QuoteStatus;

/**
 * Record every event of a kind, in order.
 *
 * @param  class-string  $kind
 * @return ArrayObject<int, object>
 */
function recordEvents(string $kind): ArrayObject
{
    /** @var ArrayObject<int, object> $seen */
    $seen = new ArrayObject;

    Event::listen($kind, function (object $event) use ($seen): void {
        $seen->append($event);
    });

    return $seen;
}

it('fires every lifecycle event of a cart', function (): void {
    $seen = recordEvents(ModelLifecycleEvent::class);

    $cart = Polycart::create('cart');
    $cart->label = 'Renamed';
    $cart->save();
    Cart::query()->find($cart->id);
    $cart->replicate();
    $cart->delete();
    $cart->restore();
    $cart->forceDelete();

    $hooks = collect($seen)
        ->filter(fn (ModelLifecycleEvent $event): bool => $event->model() instanceof Cart)
        ->map(fn (ModelLifecycleEvent $event): string => $event->hook())
        ->unique()
        ->values()
        ->all();

    expect($hooks)->toEqualCanonicalizing([
        'retrieved', 'creating', 'created', 'updating', 'updated', 'saving', 'saved',
        'deleting', 'deleted', 'restoring', 'restored', 'trashed', 'forceDeleting', 'forceDeleted', 'replicating',
    ]);
});

it('fires the lifecycle events of lines, members, paths and activity', function (): void {
    $seen = recordEvents(ModelLifecycleEvent::class);

    $sales = team(organization());
    $cart = Polycart::create('cart', person($sales), scope: $sales);
    $line = $cart->add(product());
    $cart->updateLine($line, 3);
    $cart->remove($line);

    $models = collect($seen)
        ->map(fn (ModelLifecycleEvent $event): string => class_basename($event->model()).'.'.$event->hook())
        ->unique();

    expect($models)->toContain(
        'CartLine.creating', 'CartLine.created', 'CartLine.updating', 'CartLine.updated', 'CartLine.deleting', 'CartLine.deleted',
        'CartMember.created', 'CartPath.created', 'CartActivity.created',
    );
});

it('fires the cart events for a cart subclass', function (): void {
    Event::fake([CartCreatingEvent::class]);

    $quote = Polycart::create('quote');

    expect($quote)->toBeInstanceOf(Quote::class);

    Event::assertDispatched(CartCreatingEvent::class, fn (CartCreatingEvent $event): bool => $event->cart instanceof Quote);
});

it('lets a creating listener stop a cart being created', function (): void {
    Event::listen(CartCreatingEvent::class, fn (): bool => false);

    $cart = Polycart::create('cart');

    expect($cart->exists)->toBeFalse()
        ->and(Cart::query()->count())->toBe(0);
});

it('starts and finishes every action once, in order', function (): void {
    $starts = recordEvents(ActionStartingEvent::class);
    $stops = recordEvents(ActionFinishedEvent::class);

    $sales = team(organization());
    $ann = person($sales);

    $cart = Polycart::active('cart', $ann, $sales);
    $lines = $cart->addLines([['purchasable' => product(sku: 'A')], ['purchasable' => product(sku: 'B')]]);
    $cart->updateLines([['id' => $lines[0], 'quantity' => 2]]);
    $cart->removeLines([$lines[1]]);
    $bob = person($sales);
    Polycart::share($cart, $bob, 'viewer');
    Polycart::unshare($cart, $bob);
    $cart->setVisibility(Visibility::Scope);
    app(UpdateCartAction::class)->execute($cart, ['label' => 'Lobby']);
    app(ShowCartAction::class)->execute($cart);
    app(ListCartsAction::class)->execute();
    app(ListCartTypesAction::class)->execute();
    app(ListMembersAction::class)->execute($cart);
    app(ListActivityAction::class)->execute($cart);
    $quote = $cart->convertTo('quote');
    $quote->transitionTo(QuoteStatus::Submitted);
    Polycart::merge(Polycart::create('cart', 'guest'), $cart);
    $cart->clear();
    app(DeleteCartAction::class)->execute($cart);

    // Every action that started finished, and each has its own pair of events.
    $started = collect($starts)->map(fn (object $event): string => class_basename($event))->unique();
    $finished = collect($stops)->map(fn (object $event): string => class_basename($event))->unique();

    expect($started)->toHaveCount($finished->count())
        ->and(count($starts))->toBe(count($stops))
        ->and($started->all())->toContain(
            'ActiveCartResolvingActionEvent', 'CartCreatingActionEvent', 'LinesAddingActionEvent', 'LineAddingActionEvent',
            'LinesUpdatingActionEvent', 'LineUpdatingActionEvent', 'LinesRemovingActionEvent', 'LineRemovingActionEvent',
            'CartSharingActionEvent', 'CartUnsharingActionEvent', 'VisibilityChangingActionEvent', 'CartUpdatingActionEvent',
            'CartShowingActionEvent', 'CartsListingActionEvent', 'CartTypesListingActionEvent', 'MembersListingActionEvent',
            'ActivityListingActionEvent', 'CartConvertingActionEvent', 'CartTransitioningActionEvent', 'CartsMergingActionEvent',
            'CartClearingActionEvent', 'CartDeletingActionEvent',
        );
});

it('gives every action exactly one start and one finish event', function (): void {
    $actions = glob(dirname(__DIR__, 2).'/src/Actions/*Action.php') ?: [];

    $unpaired = [];

    foreach ($actions as $path) {
        $source = (string) file_get_contents($path);
        preg_match_all('/([A-Za-z]+ActionEvent)::dispatch/', $source, $matches);

        $kinds = array_map(
            fn (string $event): string => is_subclass_of('JayI\\Polycart\\Events\\Action\\'.$event, ActionStartingEvent::class) ? 'start' : 'finish',
            $matches[1],
        );

        sort($kinds);

        if ($kinds !== ['finish', 'start']) {
            $unpaired[] = basename($path, '.php');
        }
    }

    expect($actions)->not->toBeEmpty()
        ->and($unpaired)->toBe([]);
});

it('starts before the work and finishes only once it is committed', function (): void {
    $cart = Polycart::create('cart');
    $linesAtStart = null;

    Event::listen(LineAddingActionEvent::class, function (LineAddingActionEvent $event) use (&$linesAtStart): void {
        $linesAtStart = $event->cart->lines()->count();
    });

    $finished = recordEvents(LinesAddedActionEvent::class);

    DB::transaction(function () use ($cart, $finished): void {
        $cart->addLines([['purchasable' => product()]]);

        expect($finished)->toHaveCount(0);
    });

    expect($linesAtStart)->toBe(0)
        ->and($finished)->toHaveCount(1)
        ->and($finished[0]->lines->first())->toBeInstanceOf(CartLine::class);
});

it('starts a failed action but never finishes it', function (): void {
    $starts = recordEvents(LinesAddingActionEvent::class);
    $stops = recordEvents(LinesAddedActionEvent::class);

    expect(fn (): mixed => Polycart::create('quote')->addLines([['purchasable' => product(price: null)]]))
        ->toThrow(LineRejectedException::class);

    expect($starts)->toHaveCount(1)
        ->and($stops)->toHaveCount(0);
});
