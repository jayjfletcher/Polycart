---
name: polycart-development
description: >
  Model shopping carts, saved carts, quotes, orders, and nested project carts with the
  jayi/polycart package: define CartType classes, add lines, price them, move statuses,
  convert between types, and merge guest carts in Laravel applications.
license: MIT
metadata:
  author: Jay Fletcher
---

# Polycart

Use this skill when a Laravel application needs carts, or anything shaped like a cart (a quote, an order, a wishlist, a project with sets and openings), through `jayi/polycart`.

## Primary Goal

- give every kind of cart a `CartType` class that holds its rules, so application code never branches on the type key

## Workflow

### 1. Install

```bash
composer require jayi/polycart
php artisan vendor:publish --tag="polycart-migrations"
php artisan vendor:publish --tag="polycart-config"
php artisan migrate
```

### 2. Define one type per kind of cart

- Extend `JayI\Polycart\Types\CartType` and override only what differs. The overridable methods are:
  - `statuses()` and `transitions()`
  - `requiresPrice()`, `accepts()` and `price()`
  - `convertsTo()` and `convertedFrom()`
  - `parents()` and `requiresParent()`
  - `lifetime()`
  - `mergesLines()` and `fingerprint()`
  - `prepareOptions()` and `prepareMeta()`
  - `validate()`
- Statuses are a string-backed enum. The first case is the initial status.
- Register the type in `config/polycart.php` under `types` as `'key' => Class::class`. From a package, use `Polycart::types()->register()`.
- Optionally return a `Cart` subclass from `model()`, and set `protected static ?string $cartType = 'key';` on that subclass.

### 3. Give owners carts

- Add `JayI\Polycart\Concerns\HasCarts` to the owner model. `$user->cart('quote')` returns the active cart and creates one when there is none.
- Guests: call `Polycart::active('cart', session()->getId())`. At login, call `Polycart::merge($guestCart, $user->cart())`.

### 4. Teams, organizations and sharing (optional)

- Implement `JayI\Polycart\Contracts\CartScope::parentCartScope()` on each tree level (team → organization → null).
- Implement `JayI\Polycart\Contracts\CartParticipant::cartScopes()` on users, returning the teams they are on directly.
- Start carts in a scope: `$user->cart(scope: $team)` or `Polycart::create('quote', $user, scope: $team)`. The cart is locked to that tree.
- Share: `Polycart::share($cart, $userOrTeam, 'editor')`. Only members inside the cart's top-level scope can be added. Remove with `Polycart::unshare($cart, $member)`.
- Visibility: `$cart->setVisibility(Visibility::Scope|Boundary)`.
- Check access with `$user->can('update', $cart)`, `$cart->allows($user, 'checkout')`, and `$user->accessibleCarts()`.
- Each type owns its roles in `roles()`. Guard a single status move with `transitionAbility()`.

### 5. Work with lines

- Add: `$cart->add($model, $qty, options: [...], meta: [...], unitPrice: null)`. Pass `null` as the model for a custom line.
- Add several lines, all or nothing: `$cart->addLines([['purchasable' => $model, 'quantity' => 2], ...])`. The API (`POST .../lines` with `lines: [...]`) and MCP (`add-lines`) only add in batches.
- Custom add rules (stock, customer checks, discounts) are pipeline stages. Override `CartType::addLineStages()` and use `self::insertStagesAfter(parent::addLineStages(), BuildLine::class, [CheckStock::class])`.
- A stage takes `handle(PendingLine $line, Closure $next)` and refuses with `$line->reject($message, $reason)`. Catch `LineRejectedException`, which has `reason` and `index`.
- Remove: `$cart->remove($line)` or `$cart->clear()`. Change a quantity with `$cart->updateLine($line, $qty)`. Change or remove several lines at once, all or nothing, with `$cart->updateLines([...])` and `$cart->removeLines([...])`. The API and MCP only change lines in batches (`PATCH`/`DELETE .../lines`, `update-lines`, `remove-lines`).
- Totals: `$cart->subtotal()` returns integer minor units.
- Pricing: implement `JayI\Polycart\Contracts\Purchasable::unitPriceFor()` on the model, or bind `JayI\Polycart\Contracts\PriceResolver`.

### 6. Move and convert

- Change status with `$cart->transitionTo(Enum::Case)`. Read it with `$cart->currentStatus()`.
- Convert with `$cart->convertTo('quote')`, which copies the cart, or `Polycart::convert($cart, 'order', copy: false)`, which retypes it in place.

### 7. Expose carts over HTTP, MCP, or Atrium (optional)

- JSON API: set `polycart.routes.enabled` to `true` and put your auth middleware in `polycart.routes.middleware`.
- List the models callers may name in `polycart.owners`, `polycart.scopes` and `polycart.purchasables`, keyed by alias.
- With `polycart.authorization` on (the default), every call acts as the signed-in user and is checked against the policies in `polycart.policies`. By default the cart's owner may do anything and everyone else gets what their role grants. Swap a policy by pointing its model at your own class there.
- MCP: enable `polycart.mcp.web` or `polycart.mcp.local`. The tools match the API one-to-one: `list-cart-types`, `list-carts`, `show-cart`, `create-cart`, `active-cart`, `update-cart`, `delete-cart`, `clear-cart`, `transition-cart`, `convert-cart`, `merge-carts`, `add-lines`, `update-lines`, `remove-lines`, `list-members`, `share-cart`, `unshare-cart`, `set-visibility`.
- Atrium: install `jayi/atrium`. Polycart adds Carts and Cart types pages, two widgets, and search. Set `polycart.ui.enabled` to `false` to turn this off.

### 8. Record where carts come from and what happened to them

- Every cart records a `source`. The package sets `api`, `mcp` or `atrium` itself; everything else gets `polycart.default_source` (`code`).
- Tag your own work with `Polycart::usingSource('import', fn () => ...)`, or tag routes with the `JayI\Polycart\Http\Middleware\CartSource::class.':web'` middleware.
- Every change made through the package is logged in `$cart->activities` with its action, source, actor and context. `$cart->sources` lists every source that has touched the cart. Copies and merges carry history with them.
- Record your own events with `$cart->recordActivity('exported', [...])`.
- Query with `Cart::query()->fromSource('mcp')` (creating source) or `->touchedBy('mcp')` (any touch).
- Write changes through the package's methods, not raw Eloquent updates, or they are not logged.

### 9. Let Cortex agents manage carts (optional)

- Install `jayi/cortex`. Polycart registers its MCP server as `polycart` and every MCP tool in Cortex's tool registry automatically.
- Limit the tools with `polycart.cortex.tools` (a list of names), or turn it off with `polycart.cortex.enabled`.
- Override the server instructions and tool descriptions in Cortex; the published versions are served to MCP clients and agents.
- Agent changes record `cortex` as their source.

## Rules, References, and Templates

- no additional resource files for this skill

## Examples

- A B2B shop has four types: `cart` (45-day lifetime, converts to `saved`, `quote` and `order`), `saved`, `quote` (requires prices, Draft → Submitted → Accepted, converts to `order`), and `order`. Checkout calls `$cart->convertTo('order', copy: false)`, then `->transitionTo(OrderStatus::Processing)`.
- A project carts tree has three types. `project` returns `[]` from `parents()`. `set` returns `['project']` from `parents()` and requires a parent. `opening` returns `['set']` from `parents()`, requires a parent, and returns false from `mergesLines()`.
- Events: every model fires one event class per Eloquent hook (`CartCreatingEvent`, `CartLineSavedEvent`, ...), and every action fires a start and a finish event (`LineAddingActionEvent` → `LineAddedActionEvent`). Finish events fire after commit and only on success. Listen to `ActionFinishedEvent` for all of them. In tests, fake only the events you assert on, e.g. `Event::fake([LineAddedActionEvent::class])`. Assert refusals with `LineRejectedException`, `InvalidTransitionException`, `InvalidConversionException` and `InvalidParentException`.

## Anti-patterns

- Do not branch on `$cart->type === 'quote'` in application code. Put that behaviour in the type class.
- Do not store prices as floats. Pass and read integer minor units.
- Do not share one status enum across types. Each type owns its lifecycle.
- Do not enable `polycart.routes` or `polycart.mcp.web` without auth middleware. They can read and change every cart.
- Do not write `type` or `status` columns directly. Use `convertTo()` and `transitionTo()`, which enforce the type's rules and fire events.
