# Release Notes

## [Unreleased](https://github.com/Refactor-Circus/Polycart/commits/main)

### Breaking

- Moved to the Refactor Circus organisation: the package is now `refactor-circus/polycart` with the PHP namespace `RefactorCircus\Polycart` (it was `jayi/polycart` and `JayI\Polycart`). Update `composer.json` requirements and `use` statements. Old class names are not kept as aliases, so stored values written under them - polymorphic `*_type` columns, audit subjects, Pennant feature names - need updating to the new names.

### Added

- The package's section in Atrium's sidebar rail has its own icon (`shopping-cart`) and a fixed place in the rail.
- An **Audit log** link in the package's sidebar group, opening its own audit log in Atrium (`/atrium/history/polycart`), shown while an audit log (refactor-circus/keen) is installed and to those who may read the package's history.

### Breaking

- **The cart activity log is replaced by refactor-circus/keen history.** Polycart keeps no history of its own: install [refactor-circus/keen](https://github.com/jayjfletcher/Keen), the suite-wide audit log (now in `suggest`), and every cart change is recorded there with source `polycart`; without it carts keep no history. The `Activity` domain is removed: `CartActivityModel` and its policy and model events, `ActivityRecorder`, the `Activity` enum, `ListActivityAction` and its events, `$cart->activities`, the `polycart.policies` entry for `CartActivityModel`, and the `RefactorCircus\Polycart\Models\CartActivity` morph alias.
- `GET /polycart/carts/{cart}/activity` and the `list-cart-activity` MCP tool are removed in favour of `GET /polycart/history?subject_type=...&subject_id=...` and `list-polycart-history-tool`.
- The create migration no longer creates `polycart_cart_activities` or the `polycart_carts.sources` column. No migration drops them: existing installs keep the orphaned table and column, which are harmless; drop them when you no longer need the old entries.
- `polycart_carts.source` stays, and is now refactor-circus/keystone's `Surface::current()`: the JSON API records `http` (was `api`), and work outside every surface records `cli` or `code` (was `polycart.default_source`, which is removed). `CartSource` (the enum and the middleware), `SourceContext` and `Cortex\RecordsAgentCartSource` are removed; `Polycart::usingSource()` now wraps `Surface::using()`, and `Surface::route()` replaces the middleware for your own routes.
- `$cart->sources`, `CartModel::scopeTouchedBy()`, the `touched_by` list filter and the `sources` field of cart responses are removed: which surfaces touched a cart is in its history.
- Histories are no longer copied when carts merge or convert. The cart merged into records `carts.merged` with `merged_from`, and a conversion's copy records `cart.converted` with `converted_from`, so a cart's history shows where it came from.
- `$cart->recordActivity()` is deprecated, returns nothing, and calls `Keen::record()->on($cart)` when refactor-circus/keen is installed (an action without a dot gets a `cart.` prefix); otherwise it does nothing.
- `RefactorCircus\Polycart\Support\ServiceProvider` is removed; domain providers extend Keystone's `ServiceProvider`.
- Polycart ships no stylesheet: `resources/css/atrium.css` and its `Atrium::css()` registration are removed, and every screen uses only Atrium's components and compiled utilities.

- Polycart now stands on [refactor-circus/keystone](https://github.com/jayjfletcher/Foundation), the shared runtime of the Refactor Circus packages, which it requires. Its own copies are removed in favour of Keystone's: `Contracts\ActionStartingEvent`, `Contracts\ActionFinishedEvent` and `Contracts\ModelLifecycleEvent` (now `RefactorCircus\Keystone\Contracts\*`), `Support\Models\Concerns\DispatchesModelEvents` (now `RefactorCircus\Keystone\Models\Concerns\DispatchesModelEvents`), `Support\Authorizer` (now `RefactorCircus\Keystone\Auth\Authorizer::for($package)`), `Http\Request` (now `RefactorCircus\Keystone\Http\Requests\Request`), `Mcp\Tool` (now `RefactorCircus\Keystone\Mcp\Tool`) and `Cortex\CortexIntegration` (now `RefactorCircus\Keystone\Cortex\CortexIntegration::for($package)`). Listen to the Keystone contracts to hear every action and model event of every Refactor Circus package. Config keys, route names, MCP tool names and behaviour are unchanged.
- `PolycartServiceProvider` extends Keystone's `PackageServiceProvider`, `PolycartServer` extends `RefactorCircus\Keystone\Mcp\Server`, `PolycartException` extends `RefactorCircus\Keystone\Exceptions\PackageException` (still 422), and the base policy extends `RefactorCircus\Keystone\Policies\Policy`: `allowsOnCart()` is now `allowsOn()`.
- MCP requests extending `RefactorCircus\Polycart\Mcp\Request` implement `respond(array $validated)` instead of `handle()`.
- A `PolycartException` now always renders as JSON with its status; previously a request that did not expect JSON fell through to Laravel's own handling.

- The source is reorganised into domain modules under `src/Domains/{Domain}` (`RefactorCircus\Polycart\Domains\{Domain}`): **Cart**, **CartLine**, **CartType**, **Sharing** (members, visibility and access), **Scope** (the scope tree and cart paths) and **Activity** (sources and the activity log). Each domain has its own service provider, registered by `RefactorCircus\Polycart\Domains\DomainServiceProvider`; `PolycartServiceProvider`, the `Polycart` facade, the config file and its keys, route names and URLs, MCP tool names, view and translation namespaces, publish tags and event class names are unchanged. There are no aliases for the old class names: update your imports.
- The Eloquent models are renamed to end in `Model`. Their old class names are kept as morph aliases, so polymorphic `*_type` values written under them still resolve and new rows keep writing the same values:
  - `RefactorCircus\Polycart\Models\Cart` → `RefactorCircus\Polycart\Domains\Cart\Models\CartModel`
  - `RefactorCircus\Polycart\Models\CartActivity` → `RefactorCircus\Polycart\Domains\Activity\Models\CartActivityModel`
  - `RefactorCircus\Polycart\Models\CartLine` → `RefactorCircus\Polycart\Domains\CartLine\Models\CartLineModel`
  - `RefactorCircus\Polycart\Models\CartMember` → `RefactorCircus\Polycart\Domains\Sharing\Models\CartMemberModel`
  - `RefactorCircus\Polycart\Models\CartPath` → `RefactorCircus\Polycart\Domains\Scope\Models\CartPathModel`
- `PolycartSupportFeature` moved to `RefactorCircus\Polycart\Atrium\Features` and keeps its stored Pennant name, `RefactorCircus\Polycart\Features\PolycartSupportFeature`, through Pennant's `#[Name]` attribute.
- `routes/polycart.php` is replaced by a `routes.php` per domain; the routes, their names and middleware are unchanged. `GET carts/{cart}/activity` is served by the new `Domains\Activity\Http\Controllers\CartActivityController` instead of `CartController::activity()`.
- Model events (formerly `Events\Model`) and action events (formerly `Events\Action`) now live in their domain's `Events` namespace, keeping their class names.
- Every other moved class, by its new namespace (old names relative to `RefactorCircus\Polycart`):
  - `RefactorCircus\Polycart\Support`: `Access\Authorizer`
  - `RefactorCircus\Polycart\Domains\Sharing\Services`: `Access\CartAccess`
  - `RefactorCircus\Polycart\Domains\Scope\Services`: `Access\ScopeTree`
  - `RefactorCircus\Polycart\Domains\Cart\Actions`: `Actions\ActiveCartAction`, `Actions\ClearCartAction`, `Actions\ConvertCartAction`, `Actions\CreateCartAction`, `Actions\DeleteCartAction`, `Actions\ListCartsAction`, `Actions\MergeCartsAction`, `Actions\ShowCartAction`, `Actions\TransitionCartAction`, `Actions\UpdateCartAction`
  - `RefactorCircus\Polycart\Domains\CartLine\Actions`: `Actions\AddLineAction`, `Actions\AddLinesAction`, `Actions\RemoveLineAction`, `Actions\RemoveLinesAction`, `Actions\UpdateLineAction`, `Actions\UpdateLinesAction`
  - `RefactorCircus\Polycart\Domains\Activity\Actions`: `Actions\ListActivityAction`
  - `RefactorCircus\Polycart\Domains\CartType\Actions`: `Actions\ListCartTypesAction`
  - `RefactorCircus\Polycart\Domains\Sharing\Actions`: `Actions\ListMembersAction`, `Actions\SetVisibilityAction`, `Actions\ShareCartAction`, `Actions\UnshareCartAction`
  - `RefactorCircus\Polycart\Domains\Cart\Concerns`: `Concerns\HasCarts`
  - `RefactorCircus\Polycart\Domains\CartLine\Contracts`: `Contracts\AddLineStage`, `Contracts\PriceResolver`, `Contracts\Purchasable`
  - `RefactorCircus\Polycart\Domains\Scope\Contracts`: `Contracts\CartParticipant`, `Contracts\CartScope`
  - `RefactorCircus\Polycart\Domains\Activity\Enums`: `Enums\Activity`, `Enums\CartSource`
  - `RefactorCircus\Polycart\Domains\Sharing\Enums`: `Enums\Visibility`
  - `RefactorCircus\Polycart\Domains\Cart\Events`: every model and action event for the domain (35 classes)
  - `RefactorCircus\Polycart\Domains\Activity\Events`: every model and action event for the domain (12 classes)
  - `RefactorCircus\Polycart\Domains\Sharing\Events`: every model and action event for the domain (18 classes)
  - `RefactorCircus\Polycart\Domains\CartType\Events`: every model and action event for the domain (2 classes)
  - `RefactorCircus\Polycart\Domains\CartLine\Events`: every model and action event for the domain (22 classes)
  - `RefactorCircus\Polycart\Domains\Scope\Events`: every model and action event for the domain (10 classes)
  - `RefactorCircus\Polycart\Domains\CartType\Exceptions`: `Exceptions\CartTypeCollisionException`, `Exceptions\UnknownCartTypeException`
  - `RefactorCircus\Polycart\Domains\Cart\Exceptions`: `Exceptions\InvalidConversionException`, `Exceptions\InvalidParentException`, `Exceptions\InvalidTransitionException`
  - `RefactorCircus\Polycart\Domains\Scope\Exceptions`: `Exceptions\InvalidScopeException`
  - `RefactorCircus\Polycart\Domains\CartLine\Exceptions`: `Exceptions\LineRejectedException`
  - `RefactorCircus\Polycart\Domains\Sharing\Exceptions`: `Exceptions\SharingException`
  - `RefactorCircus\Polycart\Atrium\Features`: `Features\PolycartSupportFeature`
  - `RefactorCircus\Polycart\Domains\Cart\Http\Controllers`: `Http\Controllers\CartController`
  - `RefactorCircus\Polycart\Domains\CartLine\Http\Controllers`: `Http\Controllers\CartLineController`
  - `RefactorCircus\Polycart\Domains\Sharing\Http\Controllers`: `Http\Controllers\CartMemberController`
  - `RefactorCircus\Polycart\Domains\CartType\Http\Controllers`: `Http\Controllers\CartTypeController`
  - `RefactorCircus\Polycart\Domains\Activity\Http\Middleware`: `Http\Middleware\CartSource`
  - `RefactorCircus\Polycart\Domains\Cart\Http\Requests`: `Http\Requests\ActiveCartRequest`, `Http\Requests\CartRequest`, `Http\Requests\ClearCartRequest`, `Http\Requests\ConvertCartRequest`, `Http\Requests\DestroyCartRequest`, `Http\Requests\IndexCartsRequest`, `Http\Requests\MergeCartsRequest`, `Http\Requests\ShowCartRequest`, `Http\Requests\StoreCartRequest`, `Http\Requests\TransitionCartRequest`, `Http\Requests\UpdateCartRequest`
  - `RefactorCircus\Polycart\Domains\CartLine\Http\Requests`: `Http\Requests\DestroyLinesRequest`, `Http\Requests\StoreLinesRequest`, `Http\Requests\UpdateLinesRequest`
  - `RefactorCircus\Polycart\Domains\Sharing\Http\Requests`: `Http\Requests\DestroyMemberRequest`, `Http\Requests\IndexMembersRequest`, `Http\Requests\StoreMemberRequest`, `Http\Requests\UpdateVisibilityRequest`
  - `RefactorCircus\Polycart\Domains\Activity\Http\Requests`: `Http\Requests\IndexActivityRequest`
  - `RefactorCircus\Polycart\Domains\CartType\Http\Requests`: `Http\Requests\IndexCartTypesRequest`
  - `RefactorCircus\Polycart\Domains\Activity\Resources`: `Http\Resources\CartActivityResource`
  - `RefactorCircus\Polycart\Domains\CartLine\Resources`: `Http\Resources\CartLineResource`
  - `RefactorCircus\Polycart\Domains\Sharing\Resources`: `Http\Resources\CartMemberResource`
  - `RefactorCircus\Polycart\Domains\Cart\Resources`: `Http\Resources\CartResource`
  - `RefactorCircus\Polycart\Domains\CartType\Http\Resources`: `Http\Resources\CartTypeResource`
  - `RefactorCircus\Polycart\Atrium\Http\Controllers`: `Http\Ui\CartTypeUiController`, `Http\Ui\CartUiController`
  - `RefactorCircus\Polycart\Atrium\Http\Controllers\Concerns`: `Http\Ui\Concerns\AuthorizesScreens`
  - `RefactorCircus\Polycart\Atrium`: `Http\Ui\ScreenAccess`
  - `RefactorCircus\Polycart\Domains\Cart\Mcp\Requests`: `Mcp\Requests\ActiveCartMcpRequest`, `Mcp\Requests\CartRequest`, `Mcp\Requests\ClearCartMcpRequest`, `Mcp\Requests\ConvertCartMcpRequest`, `Mcp\Requests\CreateCartMcpRequest`, `Mcp\Requests\DeleteCartMcpRequest`, `Mcp\Requests\ListCartsMcpRequest`, `Mcp\Requests\MergeCartsMcpRequest`, `Mcp\Requests\ShowCartMcpRequest`, `Mcp\Requests\TransitionCartMcpRequest`, `Mcp\Requests\UpdateCartMcpRequest`
  - `RefactorCircus\Polycart\Domains\CartLine\Mcp\Requests`: `Mcp\Requests\AddLinesMcpRequest`, `Mcp\Requests\RemoveLinesMcpRequest`, `Mcp\Requests\UpdateLinesMcpRequest`
  - `RefactorCircus\Polycart\Domains\Activity\Mcp\Requests`: `Mcp\Requests\ListActivityMcpRequest`
  - `RefactorCircus\Polycart\Domains\CartType\Mcp\Requests`: `Mcp\Requests\ListCartTypesMcpRequest`
  - `RefactorCircus\Polycart\Domains\Sharing\Mcp\Requests`: `Mcp\Requests\ListMembersMcpRequest`, `Mcp\Requests\SetVisibilityMcpRequest`, `Mcp\Requests\ShareCartMcpRequest`, `Mcp\Requests\UnshareCartMcpRequest`
  - `RefactorCircus\Polycart\Domains\Cart\Mcp\Tools`: `Mcp\Tools\ActiveCartTool`, `Mcp\Tools\ClearCartTool`, `Mcp\Tools\ConvertCartTool`, `Mcp\Tools\CreateCartTool`, `Mcp\Tools\DeleteCartTool`, `Mcp\Tools\ListCartsTool`, `Mcp\Tools\MergeCartsTool`, `Mcp\Tools\ShowCartTool`, `Mcp\Tools\TransitionCartTool`, `Mcp\Tools\UpdateCartTool`
  - `RefactorCircus\Polycart\Domains\CartLine\Mcp\Tools`: `Mcp\Tools\AddLinesTool`, `Mcp\Tools\RemoveLinesTool`, `Mcp\Tools\UpdateLinesTool`
  - `RefactorCircus\Polycart\Domains\Activity\Mcp\Tools`: `Mcp\Tools\ListActivityTool`
  - `RefactorCircus\Polycart\Domains\CartType\Mcp\Tools`: `Mcp\Tools\ListCartTypesTool`
  - `RefactorCircus\Polycart\Domains\Sharing\Mcp\Tools`: `Mcp\Tools\ListMembersTool`, `Mcp\Tools\SetVisibilityTool`, `Mcp\Tools\ShareCartTool`, `Mcp\Tools\UnshareCartTool`
  - `RefactorCircus\Polycart\Support\Models\Concerns`: `Models\Concerns\DispatchesModelEvents`
  - `RefactorCircus\Polycart\Domains\CartLine\Support`: `Pipeline\PendingLine`, `Support\LineInput`, `Support\LineLookup`
  - `RefactorCircus\Polycart\Domains\CartLine\Support\Stages`: `Pipeline\Stages\BuildLine`, `Pipeline\Stages\CheckAccepted`, `Pipeline\Stages\FindMatchingLine`, `Pipeline\Stages\PrepareLine`, `Pipeline\Stages\ResolvePrice`, `Pipeline\Stages\ValidateLine`, `Pipeline\Stages\WriteLine`
  - `RefactorCircus\Polycart\Domains\Activity\Policies`: `Policies\CartActivityPolicy`
  - `RefactorCircus\Polycart\Domains\CartLine\Policies`: `Policies\CartLinePolicy`
  - `RefactorCircus\Polycart\Domains\Sharing\Policies`: `Policies\CartMemberPolicy`
  - `RefactorCircus\Polycart\Domains\Cart\Policies`: `Policies\CartPolicy`
  - `RefactorCircus\Polycart\Support\Policies`: `Policies\Policy`
  - `RefactorCircus\Polycart\Domains\CartLine\Services`: `Pricing\PurchasablePriceResolver`
  - `RefactorCircus\Polycart\Domains\Activity\Services`: `Support\ActivityRecorder`, `Support\SourceContext`
  - `RefactorCircus\Polycart\Domains\CartType\Support`: `Types\CartType`, `Types\ShoppingCart`
  - `RefactorCircus\Polycart\Domains\CartType\Services`: `Types\CartTypeRegistry`

### Added

- The line, sharing, merge and conversion action events implement `RefactorCircus\Keystone\Audit\Contracts\Auditable`, naming the cart as the subject of their audit entry with the line, member, `merged_from` or `converted_from` details as context. Carts (by label, else id) and lines are labelled through Keystone's `AuditHooks`.
- With refactor-circus/keen installed, a cart's Atrium page shows its history (`<x-atrium::audit-trail source="polycart" :subject="$cart" />`), and the carts page Polycart's recent history to those who may read it.
- `GET {prefix}/history` (`polycart.history.index`) and the `list-polycart-history-tool` MCP tool serve Polycart's audit history from an installed audit log such as refactor-circus/keen, and answer "not installed" (404 over HTTP) until one is.
- Polycart's Atrium navigation items have icons, and the screens follow Atrium's screen conventions: actions are icon buttons with their label as a tooltip, and cart statuses are status dots (`data-status`) coloured by `RefactorCircus\Polycart\Atrium\Badges`, which keeps `info` for pending and awaiting states.
- The `@polycartCan` Blade conditional and `RefactorCircus\Polycart\Atrium\ScreenAccess`, which ask the cart policies exactly as the JSON API does.
- `RefactorCircus\Polycart\Atrium\Features\PolycartSupportFeature` and the `polycart.atrium.features` config: with refactor-circus/pennantplus installed, a global Pennant switch for Polycart in Atrium. Feature classes that cannot be loaded are skipped.
- `polycart.atrium.show_all` (default `false`): `true`, or the name of a Gate ability, makes users dashboard operators who see every cart in Atrium's lists, widgets and search and may take every action on it. The JSON API and MCP tools are unaffected.

### Fixed

- Tailwind utilities that only Polycart's Atrium screens use (`w-48`, `gap-6`, `sm:grid-cols-3`, ...) are now compiled into Atrium's stylesheet, so the **Move to** and **Convert to** selects and the cart grids are sized as intended. The **Keep the original** checkbox no longer squeezes the **Convert to** select.

### Changed

- Requires PHP 8.5 (was PHP 8.4), with dependency lower bounds raised to the latest releases (`laravel/framework` ^13.35, `orchestra/testbench` ^11.3, `pestphp/pest` ^5.3.1, `larastan/larastan` ^3.13).
- The cart page's details use `x-atrium::description-list`, and every screen shows its flash status and errors with `x-atrium::flash`; the `ui/partials/status` view is removed.
- `PolycartPlugin::features()` uses Atrium's `featuresFromConfig()`, and `ScreenAccess::allows()` delegates to Atrium's `ScreenAccess` for abilities without arguments.
- With `polycart.authorization` on, the Atrium dashboard now applies the same per-user policies as the JSON API and MCP tools. Navigation, widgets and search need `viewAny` on `Cart`; each page action checks the ability its API request checks and answers 403 otherwise; controls the user may not use are hidden; and lists, widgets and search hold only the carts the user can access. Previously the dashboard relied on Atrium's gate alone.
- `RefactorCircus\Polycart\Atrium\Format::variant()` is replaced by `Badges::forCart()`.


## [v0.1.0](https://github.com/Refactor-Circus/Polycart/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
