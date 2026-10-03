<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains;

use Illuminate\Support\ServiceProvider;
use JayI\Polycart\Domains\Activity\ActivityServiceProvider;
use JayI\Polycart\Domains\Cart\CartServiceProvider;
use JayI\Polycart\Domains\CartLine\CartLineServiceProvider;
use JayI\Polycart\Domains\CartType\CartTypeServiceProvider;
use JayI\Polycart\Domains\Scope\ScopeServiceProvider;
use JayI\Polycart\Domains\Sharing\SharingServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * The domain service providers.
     *
     * @var array<int, class-string<ServiceProvider>>
     */
    private array $providers = [
        ActivityServiceProvider::class,
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
