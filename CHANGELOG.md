# Release Notes

## [Unreleased](https://github.com/jayi/polycart/compare/v0.1.0...1.x)

### Breaking

- Polycart now stands on [jayi/foundation](https://github.com/jayjfletcher/Foundation), the shared runtime of the jayi packages, which it requires. Its own copies are removed in favour of Foundation's: `Contracts\ActionStartingEvent`, `Contracts\ActionFinishedEvent` and `Contracts\ModelLifecycleEvent` (now `JayI\Foundation\Contracts\*`), `Support\Models\Concerns\DispatchesModelEvents` (now `JayI\Foundation\Models\Concerns\DispatchesModelEvents`), `Support\Authorizer` (now `JayI\Foundation\Auth\Authorizer::for($package)`), `Http\Request` (now `JayI\Foundation\Http\Requests\Request`), `Mcp\Tool` (now `JayI\Foundation\Mcp\Tool`) and `Cortex\CortexIntegration` (now `JayI\Foundation\Cortex\CortexIntegration::for($package)`). Listen to the Foundation contracts to hear every action and model event of every jayi package. Config keys, route names, MCP tool names and behaviour are unchanged.
- `PolycartServiceProvider` extends Foundation's `PackageServiceProvider`, `PolycartServer` extends `JayI\Foundation\Mcp\Server`, `PolycartException` extends `JayI\Foundation\Exceptions\PackageException` (still 422), and the base policy extends `JayI\Foundation\Policies\Policy`: `allowsOnCart()` is now `allowsOn()`.
- MCP requests extending `JayI\Polycart\Mcp\Request` implement `respond(array $validated)` instead of `handle()`.
- A `PolycartException` now always renders as JSON with its status; previously a request that did not expect JSON fell through to Laravel's own handling.

- The source is reorganised into domain modules under `src/Domains/{Domain}` (`JayI\Polycart\Domains\{Domain}`): **Cart**, **CartLine**, **CartType**, **Sharing** (members, visibility and access), **Scope** (the scope tree and cart paths) and **Activity** (sources and the activity log). Each domain has its own service provider, registered by `JayI\Polycart\Domains\DomainServiceProvider`; `PolycartServiceProvider`, the `Polycart` facade, the config file and its keys, route names and URLs, MCP tool names, view and translation namespaces, publish tags and event class names are unchanged. There are no aliases for the old class names: update your imports.
- The Eloquent models are renamed to end in `Model`. Their old class names are kept as morph aliases, so polymorphic `*_type` values written under them still resolve and new rows keep writing the same values:
  - `JayI\Polycart\Models\Cart` → `JayI\Polycart\Domains\Cart\Models\CartModel`
  - `JayI\Polycart\Models\CartActivity` → `JayI\Polycart\Domains\Activity\Models\CartActivityModel`
  - `JayI\Polycart\Models\CartLine` → `JayI\Polycart\Domains\CartLine\Models\CartLineModel`
  - `JayI\Polycart\Models\CartMember` → `JayI\Polycart\Domains\Sharing\Models\CartMemberModel`
  - `JayI\Polycart\Models\CartPath` → `JayI\Polycart\Domains\Scope\Models\CartPathModel`
- `PolycartSupportFeature` moved to `JayI\Polycart\Atrium\Features` and keeps its stored Pennant name, `JayI\Polycart\Features\PolycartSupportFeature`, through Pennant's `#[Name]` attribute.
- `routes/polycart.php` is replaced by a `routes.php` per domain; the routes, their names and middleware are unchanged. `GET carts/{cart}/activity` is served by the new `Domains\Activity\Http\Controllers\CartActivityController` instead of `CartController::activity()`.
- Model events (formerly `Events\Model`) and action events (formerly `Events\Action`) now live in their domain's `Events` namespace, keeping their class names.
- Every other moved class, by its new namespace (old names relative to `JayI\Polycart`):
  - `JayI\Polycart\Support`: `Access\Authorizer`
  - `JayI\Polycart\Domains\Sharing\Services`: `Access\CartAccess`
  - `JayI\Polycart\Domains\Scope\Services`: `Access\ScopeTree`
  - `JayI\Polycart\Domains\Cart\Actions`: `Actions\ActiveCartAction`, `Actions\ClearCartAction`, `Actions\ConvertCartAction`, `Actions\CreateCartAction`, `Actions\DeleteCartAction`, `Actions\ListCartsAction`, `Actions\MergeCartsAction`, `Actions\ShowCartAction`, `Actions\TransitionCartAction`, `Actions\UpdateCartAction`
  - `JayI\Polycart\Domains\CartLine\Actions`: `Actions\AddLineAction`, `Actions\AddLinesAction`, `Actions\RemoveLineAction`, `Actions\RemoveLinesAction`, `Actions\UpdateLineAction`, `Actions\UpdateLinesAction`
  - `JayI\Polycart\Domains\Activity\Actions`: `Actions\ListActivityAction`
  - `JayI\Polycart\Domains\CartType\Actions`: `Actions\ListCartTypesAction`
  - `JayI\Polycart\Domains\Sharing\Actions`: `Actions\ListMembersAction`, `Actions\SetVisibilityAction`, `Actions\ShareCartAction`, `Actions\UnshareCartAction`
  - `JayI\Polycart\Domains\Cart\Concerns`: `Concerns\HasCarts`
  - `JayI\Polycart\Domains\CartLine\Contracts`: `Contracts\AddLineStage`, `Contracts\PriceResolver`, `Contracts\Purchasable`
  - `JayI\Polycart\Domains\Scope\Contracts`: `Contracts\CartParticipant`, `Contracts\CartScope`
  - `JayI\Polycart\Domains\Activity\Enums`: `Enums\Activity`, `Enums\CartSource`
  - `JayI\Polycart\Domains\Sharing\Enums`: `Enums\Visibility`
  - `JayI\Polycart\Domains\Cart\Events`: every model and action event for the domain (35 classes)
  - `JayI\Polycart\Domains\Activity\Events`: every model and action event for the domain (12 classes)
  - `JayI\Polycart\Domains\Sharing\Events`: every model and action event for the domain (18 classes)
  - `JayI\Polycart\Domains\CartType\Events`: every model and action event for the domain (2 classes)
  - `JayI\Polycart\Domains\CartLine\Events`: every model and action event for the domain (22 classes)
  - `JayI\Polycart\Domains\Scope\Events`: every model and action event for the domain (10 classes)
  - `JayI\Polycart\Domains\CartType\Exceptions`: `Exceptions\CartTypeCollisionException`, `Exceptions\UnknownCartTypeException`
  - `JayI\Polycart\Domains\Cart\Exceptions`: `Exceptions\InvalidConversionException`, `Exceptions\InvalidParentException`, `Exceptions\InvalidTransitionException`
  - `JayI\Polycart\Domains\Scope\Exceptions`: `Exceptions\InvalidScopeException`
  - `JayI\Polycart\Domains\CartLine\Exceptions`: `Exceptions\LineRejectedException`
  - `JayI\Polycart\Domains\Sharing\Exceptions`: `Exceptions\SharingException`
  - `JayI\Polycart\Atrium\Features`: `Features\PolycartSupportFeature`
  - `JayI\Polycart\Domains\Cart\Http\Controllers`: `Http\Controllers\CartController`
  - `JayI\Polycart\Domains\CartLine\Http\Controllers`: `Http\Controllers\CartLineController`
  - `JayI\Polycart\Domains\Sharing\Http\Controllers`: `Http\Controllers\CartMemberController`
  - `JayI\Polycart\Domains\CartType\Http\Controllers`: `Http\Controllers\CartTypeController`
  - `JayI\Polycart\Domains\Activity\Http\Middleware`: `Http\Middleware\CartSource`
  - `JayI\Polycart\Domains\Cart\Http\Requests`: `Http\Requests\ActiveCartRequest`, `Http\Requests\CartRequest`, `Http\Requests\ClearCartRequest`, `Http\Requests\ConvertCartRequest`, `Http\Requests\DestroyCartRequest`, `Http\Requests\IndexCartsRequest`, `Http\Requests\MergeCartsRequest`, `Http\Requests\ShowCartRequest`, `Http\Requests\StoreCartRequest`, `Http\Requests\TransitionCartRequest`, `Http\Requests\UpdateCartRequest`
  - `JayI\Polycart\Domains\CartLine\Http\Requests`: `Http\Requests\DestroyLinesRequest`, `Http\Requests\StoreLinesRequest`, `Http\Requests\UpdateLinesRequest`
  - `JayI\Polycart\Domains\Sharing\Http\Requests`: `Http\Requests\DestroyMemberRequest`, `Http\Requests\IndexMembersRequest`, `Http\Requests\StoreMemberRequest`, `Http\Requests\UpdateVisibilityRequest`
  - `JayI\Polycart\Domains\Activity\Http\Requests`: `Http\Requests\IndexActivityRequest`
  - `JayI\Polycart\Domains\CartType\Http\Requests`: `Http\Requests\IndexCartTypesRequest`
  - `JayI\Polycart\Domains\Activity\Resources`: `Http\Resources\CartActivityResource`
  - `JayI\Polycart\Domains\CartLine\Resources`: `Http\Resources\CartLineResource`
  - `JayI\Polycart\Domains\Sharing\Resources`: `Http\Resources\CartMemberResource`
  - `JayI\Polycart\Domains\Cart\Resources`: `Http\Resources\CartResource`
  - `JayI\Polycart\Domains\CartType\Http\Resources`: `Http\Resources\CartTypeResource`
  - `JayI\Polycart\Atrium\Http\Controllers`: `Http\Ui\CartTypeUiController`, `Http\Ui\CartUiController`
  - `JayI\Polycart\Atrium\Http\Controllers\Concerns`: `Http\Ui\Concerns\AuthorizesScreens`
  - `JayI\Polycart\Atrium`: `Http\Ui\ScreenAccess`
  - `JayI\Polycart\Domains\Cart\Mcp\Requests`: `Mcp\Requests\ActiveCartMcpRequest`, `Mcp\Requests\CartRequest`, `Mcp\Requests\ClearCartMcpRequest`, `Mcp\Requests\ConvertCartMcpRequest`, `Mcp\Requests\CreateCartMcpRequest`, `Mcp\Requests\DeleteCartMcpRequest`, `Mcp\Requests\ListCartsMcpRequest`, `Mcp\Requests\MergeCartsMcpRequest`, `Mcp\Requests\ShowCartMcpRequest`, `Mcp\Requests\TransitionCartMcpRequest`, `Mcp\Requests\UpdateCartMcpRequest`
  - `JayI\Polycart\Domains\CartLine\Mcp\Requests`: `Mcp\Requests\AddLinesMcpRequest`, `Mcp\Requests\RemoveLinesMcpRequest`, `Mcp\Requests\UpdateLinesMcpRequest`
  - `JayI\Polycart\Domains\Activity\Mcp\Requests`: `Mcp\Requests\ListActivityMcpRequest`
  - `JayI\Polycart\Domains\CartType\Mcp\Requests`: `Mcp\Requests\ListCartTypesMcpRequest`
  - `JayI\Polycart\Domains\Sharing\Mcp\Requests`: `Mcp\Requests\ListMembersMcpRequest`, `Mcp\Requests\SetVisibilityMcpRequest`, `Mcp\Requests\ShareCartMcpRequest`, `Mcp\Requests\UnshareCartMcpRequest`
  - `JayI\Polycart\Domains\Cart\Mcp\Tools`: `Mcp\Tools\ActiveCartTool`, `Mcp\Tools\ClearCartTool`, `Mcp\Tools\ConvertCartTool`, `Mcp\Tools\CreateCartTool`, `Mcp\Tools\DeleteCartTool`, `Mcp\Tools\ListCartsTool`, `Mcp\Tools\MergeCartsTool`, `Mcp\Tools\ShowCartTool`, `Mcp\Tools\TransitionCartTool`, `Mcp\Tools\UpdateCartTool`
  - `JayI\Polycart\Domains\CartLine\Mcp\Tools`: `Mcp\Tools\AddLinesTool`, `Mcp\Tools\RemoveLinesTool`, `Mcp\Tools\UpdateLinesTool`
  - `JayI\Polycart\Domains\Activity\Mcp\Tools`: `Mcp\Tools\ListActivityTool`
  - `JayI\Polycart\Domains\CartType\Mcp\Tools`: `Mcp\Tools\ListCartTypesTool`
  - `JayI\Polycart\Domains\Sharing\Mcp\Tools`: `Mcp\Tools\ListMembersTool`, `Mcp\Tools\SetVisibilityTool`, `Mcp\Tools\ShareCartTool`, `Mcp\Tools\UnshareCartTool`
  - `JayI\Polycart\Support\Models\Concerns`: `Models\Concerns\DispatchesModelEvents`
  - `JayI\Polycart\Domains\CartLine\Support`: `Pipeline\PendingLine`, `Support\LineInput`, `Support\LineLookup`
  - `JayI\Polycart\Domains\CartLine\Support\Stages`: `Pipeline\Stages\BuildLine`, `Pipeline\Stages\CheckAccepted`, `Pipeline\Stages\FindMatchingLine`, `Pipeline\Stages\PrepareLine`, `Pipeline\Stages\ResolvePrice`, `Pipeline\Stages\ValidateLine`, `Pipeline\Stages\WriteLine`
  - `JayI\Polycart\Domains\Activity\Policies`: `Policies\CartActivityPolicy`
  - `JayI\Polycart\Domains\CartLine\Policies`: `Policies\CartLinePolicy`
  - `JayI\Polycart\Domains\Sharing\Policies`: `Policies\CartMemberPolicy`
  - `JayI\Polycart\Domains\Cart\Policies`: `Policies\CartPolicy`
  - `JayI\Polycart\Support\Policies`: `Policies\Policy`
  - `JayI\Polycart\Domains\CartLine\Services`: `Pricing\PurchasablePriceResolver`
  - `JayI\Polycart\Domains\Activity\Services`: `Support\ActivityRecorder`, `Support\SourceContext`
  - `JayI\Polycart\Domains\CartType\Support`: `Types\CartType`, `Types\ShoppingCart`
  - `JayI\Polycart\Domains\CartType\Services`: `Types\CartTypeRegistry`

### Added

- `GET {prefix}/history` (`polycart.history.index`) and the `list-polycart-history-tool` MCP tool serve Polycart's audit history from an installed audit log such as jayi/keen, and answer "not installed" (404 over HTTP) until one is.
- Polycart's Atrium navigation items have icons, and the screens follow Atrium's screen conventions: actions are icon buttons with their label as a tooltip, and cart statuses are status dots (`data-status`) coloured by `JayI\Polycart\Atrium\Badges`, which keeps `info` for pending and awaiting states.
- The `@polycartCan` Blade conditional and `JayI\Polycart\Atrium\ScreenAccess`, which ask the cart policies exactly as the JSON API does.
- `JayI\Polycart\Atrium\Features\PolycartSupportFeature` and the `polycart.atrium.features` config: with jayi/pennantplus installed, a global Pennant switch for Polycart in Atrium. Feature classes that cannot be loaded are skipped.
- `polycart.atrium.show_all` (default `false`): `true`, or the name of a Gate ability, makes users dashboard operators who see every cart in Atrium's lists, widgets and search and may take every action on it. The JSON API and MCP tools are unaffected.

### Fixed

- Tailwind utilities that only Polycart's Atrium screens use (`w-48`, `gap-6`, `sm:grid-cols-3`, ...) are now added to the dashboard through `Atrium::css()`, so the **Move to** and **Convert to** selects and the cart grids are sized as intended. The **Keep the original** checkbox no longer squeezes the **Convert to** select.

### Changed

- With `polycart.authorization` on, the Atrium dashboard now applies the same per-user policies as the JSON API and MCP tools. Navigation, widgets and search need `viewAny` on `Cart`; each page action checks the ability its API request checks and answers 403 otherwise; controls the user may not use are hidden; and lists, widgets and search hold only the carts the user can access. Previously the dashboard relied on Atrium's gate alone.
- `JayI\Polycart\Atrium\Format::variant()` is replaced by `Badges::forCart()`.


## [v0.1.0](https://github.com/jayi/polycart/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
