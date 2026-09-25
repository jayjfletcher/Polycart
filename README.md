<div align="center">
    <h1>Polycart</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/jayi/polycart"><img src="https://img.shields.io/packagist/v/jayi/polycart.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/jayi/polycart"><img src="https://img.shields.io/packagist/php-v/jayi/polycart.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/jayi/polycart"><img src="https://badge.laravel.cloud/badge/jayi/polycart?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/jayi/polycart/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/jayi/polycart/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/jayi/polycart"><img src="https://img.shields.io/packagist/dt/jayi/polycart.svg?style=flat-square" alt="Total Downloads"></a>
</p>

Typed carts for Laravel. A shopping cart, a saved cart, a quote, an order, a project with its sets and openings: every one is a row in the same table, told apart by a string **type key**. Each key points at a `CartType` class, and that class holds all of that kind's rules:

- its statuses and the moves allowed between them
- what it may hold, and whether every line needs a price
- which types it can be converted into
- where it may sit in a tree of carts
- how long it lives
- whether a repeat add merges into the existing line

Your code never branches on the key.

## Installation

```bash
composer require jayi/polycart

php artisan vendor:publish --tag="polycart-migrations"
php artisan vendor:publish --tag="polycart-config"
php artisan migrate
```

The migrations create `polycart_carts`, `polycart_cart_lines`, `polycart_cart_members`, `polycart_cart_paths` and `polycart_cart_activities`. Prices are stored as integers in minor units (cents).

To customise the Atrium dashboard's views or strings, publish them with the `polycart-views` or `polycart-lang` tag. The `polycart` tag publishes everything at once.

## Defining types

A type extends `JayI\Polycart\Types\CartType` and overrides only what makes it different. Every method has a permissive default.

```php
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Types\CartType;

enum QuoteStatus: string
{
    case Draft = 'draft';          // the first case is where a new quote starts
    case Submitted = 'submitted';
    case Accepted = 'accepted';
}

class QuoteCart extends CartType
{
    public function model(): string            { return Quote::class; }   // optional Cart subclass
    public function statuses(): string         { return QuoteStatus::class; }
    public function requiresPrice(): bool      { return true; }
    public function convertsTo(): array        { return ['order']; }

    public function transitions(): array
    {
        return [
            QuoteStatus::Draft->value => [QuoteStatus::Submitted],
            QuoteStatus::Submitted->value => [QuoteStatus::Accepted],
        ];
    }

    // Keep only the header fields a quote cares about.
    public function convertedFrom(Cart $cart, Cart $source): void
    {
        $cart->meta = array_intersect_key($source->meta ?? [], array_flip(['po_number']));
    }
}
```

Register types in `config/polycart.php`:

```php
'types' => [
    'cart'  => App\Carts\ShoppingCart::class,
    'saved' => App\Carts\SavedCart::class,
    'quote' => App\Carts\QuoteCart::class,
    'order' => App\Carts\OrderCart::class,
],
```

A package can register its own types from a service provider with `Polycart::types()->register('wishlist', WishlistCart::class)`. The application's config always wins over a package registration. Two packages claiming the same key throw a `CartTypeCollisionException`.

| Method | Default | Purpose |
| --- | --- | --- |
| `model()` | `Cart::class` | Model class that rows of this type hydrate as |
| `statuses()` / `transitions()` | none / any move | This type's own lifecycle, as a string-backed enum |
| `lifetime()` | never expires | A `CarbonInterval`, pushed back on every change |
| `parents()` / `requiresParent()` | any / false | Where the type may sit in a tree |
| `convertsTo()` / `convertedFrom()` | none / no-op | Allowed conversions, and a hook on the target type |
| `accepts()` | anything | Which purchasables may be added (`null` means a custom line) |
| `requiresPrice()` / `price()` | false / `PriceResolver` | Pricing rules |
| `mergesLines()` / `fingerprint()` | true / model + options | What counts as "the same line" |
| `prepareOptions()` / `prepareMeta()` | unchanged | Filter or normalise line data before it is stored |
| `validate()` | price check | Refuse a line by throwing `LineRejectedException` |

## Typed models

Point a type at a subclass of `Cart` to give it its own relations and methods. A subclass pins itself to its key with `$cartType`:

```php
class Quote extends Cart
{
    protected static ?string $cartType = 'quote';
}

Quote::query()->get();     // only quotes
Quote::create();           // type is 'quote'
Cart::find($id);           // a Quote instance when the row is a quote
```

## Using carts

Add `HasCarts` to anything that owns carts, such as a user, a team or a customer:

```php
use JayI\Polycart\Concerns\HasCarts;

class User extends Authenticatable
{
    use HasCarts;
}

$cart = $user->cart();              // active cart of the default type, created if missing
$quote = $user->cart('quote');
$user->carts;                       // every cart of every type
```

Guests use their session key as the owner:

```php
use JayI\Polycart\Facades\Polycart;

$cart = Polycart::active('cart', session()->getId());

// At login, move the guest's lines into the user's cart.
Polycart::merge($cart, $user->cart());
```

A cart stays *active* while it has not expired and is still in its type's first status. Once it is submitted or checked out, the next `active()` call starts a new one.

### Lines

```php
$line = $cart->add($product, 2, options: ['finish' => '626', 'keying' => ['system' => 'A1']]);
$cart->add($product, 1, options: ['keying' => ['system' => 'A1'], 'finish' => '626']);  // adds to the same line: quantity 3

$cart->add(null, 1, meta: ['description' => 'Non-catalog hinge'], unitPrice: 900);   // custom line

$cart->remove($line);
$cart->clear();

$cart->subtotal();        // int, in minor units
$cart->quantity();
$cart->isFullyPriced();
```

**Options** say *what* is being bought and are part of the line's identity. **Meta** is anything else the line carries, such as a note or a reference. A custom line has no model, so its meta is part of its identity as well. To change a quantity, use `$cart->updateLine($line, 5)`, or several at once with `$cart->updateLines([['id' => $line, 'quantity' => 5], ...])`. A quantity of `0` removes the line. `$cart->removeLines([$a, $b])` removes several lines at once. Both are all or nothing.

To add several lines at once, all or nothing:

```php
$cart->addLines([
    ['purchasable' => $lock, 'quantity' => 12],
    ['purchasable' => null, 'meta' => ['description' => 'Delivery'], 'unit_price' => 2500],
]);
```

### The add pipeline

Every add goes through a pipeline of stages that the cart type controls:

1. prepare
2. check accepted
3. find matching line
4. resolve price
5. build
6. validate
7. write

Slot in your own stages for stock, customer rules or pricing, and refuse a line with a reason code:

```php
class WholesaleCart extends CartType
{
    public function addLineStages(): array
    {
        return self::insertStagesAfter(parent::addLineStages(), BuildLine::class, [CheckStock::class]);
    }
}

final class CheckStock
{
    public function handle(PendingLine $line, Closure $next): PendingLine
    {
        if ($line->resultingQuantity() > $line->purchasable->stock) {
            $line->reject("Only {$line->purchasable->stock} left.", 'out_of_stock');
        }

        return $next($line);
    }
}
```

A refused line writes nothing. The API answers `422` with `{message, reason, line}`.

**Full guide:** [Adding lines](docs/adding-lines.md). It covers every default stage, the pending line, writing and configuring stages, batches, the API and MCP, and transactions and events.

### Pricing

A line added without `unitPrice` is priced by the type's `price()`, which defers to the bound `JayI\Polycart\Contracts\PriceResolver`. By default the resolver asks models that implement `Purchasable`:

```php
class Product extends Model implements Purchasable
{
    public function unitPriceFor(Cart $cart, array $options): ?int
    {
        return $this->price_cents;
    }
}
```

To price from an ERP or a customer price book, bind your own resolver:

```php
$this->app->singleton(PriceResolver::class, ErpPriceResolver::class);
```

### Statuses

```php
$quote->transitionTo(QuoteStatus::Submitted);
$quote->currentStatus();                         // QuoteStatus::Submitted
$quote->hasStatus('submitted');                  // true

Cart::ofType('quote')->whereStatus(QuoteStatus::Submitted)->get();
```

A status that belongs to another type, or a move that is not in `transitions()`, throws `InvalidTransitionException`.

### Conversions

```php
$quote = $cart->convertTo('quote');                     // copy: the cart is left untouched
$order = Polycart::convert($quote, 'order', copy: false); // retype in place
```

The source type must list the target in `convertsTo()`. Every line is checked against the target's rules inside a transaction. For example, a quote that requires prices refuses a cart with unpriced lines, and nothing is written.

### Trees

```php
$project = Polycart::create('project', $team, ['label' => 'Tower A']);
$set = Polycart::create('set', $team, ['parent_id' => $project->id]);
$opening = Polycart::create('opening', $team, ['parent_id' => $set->id]);

$opening->root;              // $project
$project->children;          // [$set]
$project->descendants;       // [$set, $opening]
```

`parents()` and `requiresParent()` on each type decide which nestings are allowed.

## Teams, organizations and sharing

A cart can live in your application's tree, such as a team inside an organization. It stays there for good, and can be shared with people or whole teams inside the same organization.

```php
class Team extends Model implements CartScope
{
    public function parentCartScope(): ?CartScope { return $this->organization; }
}

class User extends Authenticatable implements CartParticipant
{
    use HasCarts;

    public function cartScopes(): iterable { return $this->teams; }
}

$cart = $user->cart(scope: $salesTeam);          // one active cart per team
Polycart::share($cart, $colleague, 'editor');    // anyone inside the organization
Polycart::share($cart, $opsTeam, 'viewer');      // whoever is on Ops, now and later
$cart->setVisibility(Visibility::Scope);         // the whole team can see it

$user->can('update', $cart);
$user->accessibleCarts()->get();
Cart::query()->inScope($organization)->get();
```

- New carts are private to their creator.
- Access is worked out each time it is checked.
- Unscoped carts keep working.

**Full guide:** [Scoping and sharing](docs/scoping-and-sharing.md). It covers the tree contracts, the lock, members, visibility, access rules, querying, the API and MCP, events, exceptions and storage.

### Roles belong to the cart type

Each type lists its roles, strongest first, and the abilities each grants. Types with a checkout can have a role that may check out, and types without one simply don't:

```php
public function roles(): array
{
    return [
        'owner' => ['*'],
        'buyer' => ['view', 'update', 'checkout'],
        'editor' => ['view', 'update'],
        'viewer' => ['view'],
    ];
}

public function transitionAbility(?BackedEnum $from, BackedEnum $to): string
{
    return $to === OrderStatus::Processing ? 'checkout' : 'transition';
}

$user->can('checkout', $order);
```

**Full guide:** [Roles and permissions](docs/roles-and-permissions.md). It covers the default roles, custom abilities, guarding status moves and conversions, visibility and creator roles, role mapping on conversion, what the API and MCP check, replacing the policy, and worked examples.

## JSON API

Every cart operation is available over HTTP. The API is **off by default** because it can read and change every cart. Enable it behind your own authentication:

```php
// config/polycart.php
'routes' => [
    'enabled' => true,
    'prefix' => 'polycart',
    'middleware' => ['api', 'auth:sanctum', 'can:manage-carts'],
],

// The only models the API and MCP tools may name, by alias.
'owners' => ['user' => App\Models\User::class],
'scopes' => ['team' => App\Models\Team::class, 'organization' => App\Models\Organization::class],
'purchasables' => ['product' => App\Models\Product::class],
```

With `polycart.authorization` on (the default), every call acts as the authenticated user:

- Lists show only the carts that user can access.
- New carts are owned by that user.
- Every call goes through the Gate, using the policies in `polycart.policies`. By default a cart's owner may do anything with it and everyone else gets what their role grants. Point a model at your own policy class to replace it.

Turn authorization off only for trusted server-to-server use.

| Method | Path | Does |
| --- | --- | --- |
| `GET` | `/types` | Every type and what it allows |
| `GET` | `/carts` | List, filtered by `type`, `status`, `source`, `touched_by`, `owner_type` and `owner_id`, `scope_type` and `scope_id`, `session_key`, `parent`, `root`, `search`, `unexpired`; cursor paginated |
| `POST` | `/carts` | Create a cart (`type`, and optionally `scope_type` and `scope_id`, an owner or `session_key`, `label`, `meta`, `parent_id`) |
| `POST` | `/carts/active` | The owner's current cart of a type, created if missing |
| `GET` | `/carts/{cart}` | The cart with its lines, quantity and subtotal |
| `GET` | `/carts/{cart}/activity` | The cart's activity log, filterable by `source` and `action` |
| `PATCH` | `/carts/{cart}` | Change `label` or `meta` |
| `DELETE` | `/carts/{cart}` | Soft-delete the cart |
| `POST` | `/carts/{cart}/clear` | Remove every line |
| `POST` | `/carts/{cart}/status` | Move to another `status` |
| `POST` | `/carts/{cart}/convert` | Convert `to` another type, with `copy` true or false |
| `POST` | `/carts/{cart}/merge` | Move every line `into` another cart |
| `POST` | `/carts/{cart}/lines` | Add one or more `lines`, all or nothing. Each has `purchasable_type` and `purchasable_id` (or neither, for a custom line), `quantity`, `options`, `meta`, `unit_price`. |
| `PATCH` | `/carts/{cart}/lines` | Change one or more `lines`, all or nothing. Each has `id`, `quantity` (`0` removes the line) and optionally `unit_price`. Returns the cart with its totals. |
| `DELETE` | `/carts/{cart}/lines` | Remove one or more `lines` by id, all or nothing, from the body or the query string. Returns the cart with its totals. |
| `GET` | `/carts/{cart}/members` | Who the cart is shared with |
| `POST` | `/carts/{cart}/members` | Share with a `member_type` and `member_id` (a person or a scope) as a `role` |
| `DELETE` | `/carts/{cart}/members/{member}` | Remove a member |
| `PUT` | `/carts/{cart}/visibility` | Set `visibility` to `private`, `scope` or `boundary` |

When a type refuses a request, the API returns `422` with a message that explains why, such as `A [quote] cart cannot move from [submitted] to [draft].`

## MCP server

The same operations are available as MCP tools, so an agent can manage carts:

- `list-cart-types`
- `list-carts`, `show-cart`, `create-cart`, `active-cart`, `update-cart`, `delete-cart`, `clear-cart`
- `transition-cart`, `convert-cart`, `merge-carts`
- `add-lines`, `update-lines`, `remove-lines`
- `list-members`, `share-cart`, `unshare-cart`, `set-visibility`
- `list-cart-activity`

```php
'mcp' => [
    'web' => ['enabled' => true, 'route' => 'mcp/polycart', 'middleware' => ['auth:sanctum']],
    'local' => ['enabled' => true, 'handle' => 'polycart'],
],
```

The JSON API, the MCP tools and the dashboard all call the same action classes and share their validation rules. An architecture test fails the build if any action is missing from the JSON API or the MCP server.

## Cortex

When [`jayi/cortex`](https://github.com/jayjfletcher/cortex) is installed, Polycart connects to it:

- Every MCP tool joins Cortex's tool registry, so **Cortex agents can manage carts**. They go through the same validation, authorization and add pipeline as MCP clients.
- The server is registered as `polycart`, so its **instructions and each tool's description can be overridden** with Cortex's versioned, publishable content.
- Changes an agent makes record **`cortex` as their source**.

```php
'cortex' => ['enabled' => true, 'server' => 'polycart', 'tools' => null],   // or a list of tool names
```

**Full guide:** [Cortex](docs/cortex.md).

## Atrium dashboard

When [`jayi/atrium`](https://github.com/jayjfletcher/Atrium) is installed, Polycart adds pages to the dashboard behind Atrium's own gate:

- **Carts:** filter the list, and open a cart to see its place in the tree and manage members and visibility. You can also edit its line quantities, move its status, convert it, rename it, edit its meta, clear it or delete it. Only the statuses and conversions its type allows are offered.
- **Cart types:** every registered type and its rules.
- **Widgets:** *Carts by type* and *Recent carts*.
- **Search:** find carts from Atrium's search.

Set `polycart.ui.enabled` to `false` to leave the dashboard out.

## Sources and activity

Polycart records where every change to a cart came from. The package sets `api`, `mcp`, `atrium` and `cortex` (a Cortex agent) itself; everything else gets `code`, or your `polycart.default_source`. Any other string works too.

- **`source`**: where the cart was created, or last converted.
- **`sources`**: every source that has touched the cart, in the order each first did.
- **Activity log**: each change's action, source, signed-in user, context and time.

When a cart is converted by copy or merged into another, its history goes with it. A cart built through the API and turned into an order through MCP is an `mcp` order whose `sources` are `['api', 'mcp']`.

```php
Polycart::usingSource('import', fn () => $importer->run());      // tag your own work
Route::middleware(CartSource::class.':web')->group(...);         // or your own routes

$cart->sources;                                                  // ['api', 'mcp']
$cart->activities;                                               // oldest first
$cart->recordActivity('exported', ['reference' => 'SO-1042']);    // your own events

Cart::query()->touchedBy('mcp')->get();
Cart::query()->fromSource('api')->get();
```

**Full guide:** [Sources and activity](docs/sources-and-activity.md). It covers setting sources, every recorded action and its context, how history travels through conversions and merges, the API and MCP, the dashboard, storage, and what is not recorded.

## Events

Every event carries the models involved. Events for a removed line or member carry ids, because the row is gone:

- **Model events:** every Eloquent hook of every model, one class per hook, such as `CartCreatingEvent`, `CartLineDeletedEvent` or `CartMemberSavedEvent`. Cart subclasses fire the `Cart*` events.
- **Action events:** a start and a finish event for every action, such as `LineAddingActionEvent` and `LineAddedActionEvent`, or `CartConvertingActionEvent` and `CartConvertedActionEvent`. Start fires before the work. Finish fires after the transaction commits, and only on success.
- **Listening to a whole family:** listen to `ModelLifecycleEvent`, `ActionStartingEvent` or `ActionFinishedEvent` to receive every event of that family.

**Full guide:** [Events](docs/events.md). It lists every action with its two events and what they carry.

## Pruning

A cart whose type has a `lifetime()` gets an `expires_at`. It is pruned `polycart.prune_after_days` days after it expires:

```php
Schedule::command('model:prune', ['--model' => [\JayI\Polycart\Models\Cart::class]])->daily();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Polycart! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Jay Fletcher](https://github.com/jayi)
- [All Contributors](../../contributors)

## License

Polycart is open-sourced software licensed under the [MIT license](LICENSE.md).
