<?php

namespace Workbench\App\Providers;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Workbench\App\Carts\OrderCart;
use Workbench\App\Carts\QuoteCart;
use Workbench\App\Carts\ShopCart;
use Workbench\App\Carts\WishlistCart;
use Workbench\App\Http\Middleware\SignInWorkbenchUser;
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
            // jayi/pennantplus's layered store: users who follow a feature's
            // global value store nothing, as in a real application.
            'pennant.default' => 'pennantplus',
            'pennant.stores.pennantplus' => [
                'driver' => 'pennantplus',
                'connection' => null,
                'table' => 'features',
            ],
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
        // Keep the workbench user signed in whatever URL is opened first.
        $this->callAfterResolving(HttpKernel::class, function (HttpKernel $kernel): void {
            if ($kernel instanceof Kernel) {
                $kernel->appendMiddlewareToGroup('web', SignInWorkbenchUser::class);
            }
        });

        //
    }
}
