<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Workbench\App\Carts\OrderCart;
use Workbench\App\Carts\QuoteCart;
use Workbench\App\Carts\ShopCart;
use Workbench\App\Carts\WishlistCart;
use Workbench\App\Models\Organization;
use Workbench\App\Models\Product;
use Workbench\App\Models\Team;
use Workbench\App\Models\User;

/**
 * Turns the workbench into a demo store: Polycart on the workbench User, with
 * its own cart types, teams and catalogue, shown in the Atrium dashboard.
 */
class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        config([
            'auth.providers.users.model' => User::class,
            'polycart.types' => [
                'cart' => ShopCart::class,
                'quote' => QuoteCart::class,
                'order' => OrderCart::class,
                'wishlist' => WishlistCart::class,
            ],
            'polycart.owners' => ['user' => User::class],
            'polycart.scopes' => ['team' => Team::class, 'organization' => Organization::class],
            'polycart.purchasables' => ['product' => Product::class],
            'polycart.ui.enabled' => true,
            // The demo user is an operator, so every seeded cart is on show.
            'polycart.atrium.show_all' => true,
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
