<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains;

use Illuminate\Support\ServiceProvider;
use RefactorCircus\Polycart\Domains\Cart\CartServiceProvider;
use RefactorCircus\Polycart\Domains\CartLine\CartLineServiceProvider;
use RefactorCircus\Polycart\Domains\CartType\CartTypeServiceProvider;
use RefactorCircus\Polycart\Domains\Scope\ScopeServiceProvider;
use RefactorCircus\Polycart\Domains\Sharing\SharingServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * The domain service providers.
     *
     * @var array<int, class-string<ServiceProvider>>
     */
    private array $providers = [
        CartServiceProvider::class,
        CartLineServiceProvider::class,
        CartTypeServiceProvider::class,
        ScopeServiceProvider::class,
        SharingServiceProvider::class,
    ];

    public function register(): void
    {
        foreach ($this->providers as $provider) {
            $this->app->register($provider);
        }
    }
}
