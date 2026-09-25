<?php

declare(strict_types=1);

use Illuminate\Support\ServiceProvider;
use JayI\Polycart\Contracts\PriceResolver;
use JayI\Polycart\Polycart;
use JayI\Polycart\PolycartServiceProvider;
use JayI\Polycart\Pricing\PurchasablePriceResolver;

it('resolves the entry point as a singleton', function (): void {
    expect(app(Polycart::class))->toBe(app(Polycart::class));
});

it('merges the package config', function (): void {
    expect(config('polycart.default_type'))->toBe('cart')
        ->and(config('polycart.prune_after_days'))->toBe(30);
});

it('prices through purchasables by default', function (): void {
    expect(app(PriceResolver::class))->toBeInstanceOf(PurchasablePriceResolver::class);
});

it('publishes the config and migrations', function (string $tag): void {
    expect(ServiceProvider::pathsToPublish(PolycartServiceProvider::class, $tag))->not->toBeEmpty();
})->with(['polycart', 'polycart-config', 'polycart-migrations']);
