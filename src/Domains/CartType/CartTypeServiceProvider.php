<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartType;

use JayI\Foundation\Support\ServiceProvider;
use JayI\Polycart\Domains\CartType\Services\CartTypeRegistry;

class CartTypeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CartTypeRegistry::class);
    }

    public function boot(): void
    {
        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
