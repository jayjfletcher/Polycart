<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine;

use JayI\Polycart\Domains\CartLine\Contracts\PriceResolver;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;
use JayI\Polycart\Domains\CartLine\Services\PurchasablePriceResolver;
use JayI\Polycart\Support\ServiceProvider;

class CartLineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind your own to price lines from an ERP or a price book.
        $this->app->singleton(PriceResolver::class, PurchasablePriceResolver::class);
    }

    public function boot(): void
    {
        $this->keepMorphAliases([
            'JayI\Polycart\Models\CartLine' => CartLineModel::class,
        ]);

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
