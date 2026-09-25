<?php

declare(strict_types=1);

use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartActivity;
use JayI\Polycart\Models\CartLine;
use JayI\Polycart\Models\CartMember;
use JayI\Polycart\Policies\CartActivityPolicy;
use JayI\Polycart\Policies\CartLinePolicy;
use JayI\Polycart\Policies\CartMemberPolicy;
use JayI\Polycart\Policies\CartPolicy;
use JayI\Polycart\Types\ShoppingCart;

return [

    /*
    |--------------------------------------------------------------------------
    | Cart Types
    |--------------------------------------------------------------------------
    |
    | Every kind of cart your application keeps — a shopping cart, a saved
    | cart, a quote, an order, a project — keyed by the string stored in the
    | carts table. Each class extends JayI\Polycart\Types\CartType and holds
    | that kind's rules. Entries here win over types a package registers.
    |
    */

    'types' => [
        'cart' => ShoppingCart::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Type
    |--------------------------------------------------------------------------
    |
    | The type $user->cart() returns when no type is given.
    |
    */

    'default_type' => 'cart',

    /*
    |--------------------------------------------------------------------------
    | Default Source
    |--------------------------------------------------------------------------
    |
    | What new carts record as their source when nothing more specific is
    | set. The JSON API, MCP server and dashboard set their own; wrap other
    | work in Polycart::usingSource() or the CartSource middleware.
    |
    */

    'default_source' => 'code',

    /*
    |--------------------------------------------------------------------------
    | Pruning
    |--------------------------------------------------------------------------
    |
    | Carts whose type gives them a lifetime are pruned by `model:prune` this
    | many days after they expire.
    |
    */

    'prune_after_days' => 30,

    /*
    |--------------------------------------------------------------------------
    | Owners, Scopes and Purchasables
    |--------------------------------------------------------------------------
    |
    | The models the JSON API and MCP tools may name, keyed by the alias a
    | caller uses. Owners are people (users); scopes are the levels of your
    | tree (teams, organizations) and implement CartScope. Nothing outside
    | these lists can be reached through them, so a caller can never make
    | the package load an arbitrary class. Code that calls the package
    | directly is not limited by them.
    |
    */

    'owners' => [
        // 'user' => App\Models\User::class,
    ],

    'scopes' => [
        // 'team' => App\Models\Team::class,
        // 'organization' => App\Models\Organization::class,
    ],

    'purchasables' => [
        // 'product' => App\Models\Product::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    |
    | When on, the JSON API and MCP tools act as the authenticated user: they
    | list only the carts that user can access, make them the owner of carts
    | they start, and check every change against the role the cart's type
    | gives them. Turn it off only for trusted server-to-server use.
    |
    */

    'authorization' => true,

    /*
    |--------------------------------------------------------------------------
    | Policies
    |--------------------------------------------------------------------------
    |
    | The policy the Gate uses for each model. The JSON API and MCP tools
    | check every call against these. By default a cart's owner may do
    | anything, everyone else gets what their role grants, and lines, members
    | and activity follow their cart. Point a model at your own class to
    | replace its policy; the Cart entry covers every cart type's subclass.
    |
    */

    'policies' => [
        Cart::class => CartPolicy::class,
        CartLine::class => CartLinePolicy::class,
        CartMember::class => CartMemberPolicy::class,
        CartActivity::class => CartActivityPolicy::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | JSON API
    |--------------------------------------------------------------------------
    |
    | Every cart operation over HTTP. It is off by default because it can
    | read and change every cart: enable it behind your own auth middleware.
    |
    */

    'routes' => [
        'enabled' => false,
        'prefix' => 'polycart',
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | MCP Server
    |--------------------------------------------------------------------------
    |
    | The same operations as the JSON API, as MCP tools for agents. Expose it
    | over HTTP, locally over stdio, or both.
    |
    */

    'mcp' => [
        'web' => [
            'enabled' => false,
            'route' => 'mcp/polycart',
            'middleware' => [],
        ],
        'local' => [
            'enabled' => false,
            'handle' => 'polycart',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cortex
    |--------------------------------------------------------------------------
    |
    | When jayi/cortex is installed, the MCP server is registered with it, so
    | its instructions can be overridden, and the tools join its registry,
    | so Cortex agents can manage carts. Set `tools` to a list of tool names,
    | such as ['list-carts', 'show-cart'], to offer only some of them.
    |
    */

    'cortex' => [
        'enabled' => true,
        'server' => 'polycart',
        'tools' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    |
    | When jayi/atrium is installed, Polycart adds its pages, widgets, and
    | search to the Atrium dashboard, behind Atrium's own authorization.
    |
    */

    'ui' => [
        'enabled' => true,
    ],

];
