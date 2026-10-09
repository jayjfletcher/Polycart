<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartType;

use RefactorCircus\Keystone\Support\ServiceProvider;
use RefactorCircus\Polycart\Domains\CartType\Services\CartTypeRegistry;

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
