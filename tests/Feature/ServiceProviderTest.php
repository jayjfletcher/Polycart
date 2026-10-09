<?php

declare(strict_types=1);

use Illuminate\Support\ServiceProvider;
use RefactorCircus\Keystone\Auth\Authorizer;
use RefactorCircus\Keystone\Packages\PackageRegistry;
use RefactorCircus\Polycart\Domains\CartLine\Contracts\PriceResolver;
use RefactorCircus\Polycart\Domains\CartLine\Services\PurchasablePriceResolver;
use RefactorCircus\Polycart\Mcp\PolycartServer;
use RefactorCircus\Polycart\Polycart;
use RefactorCircus\Polycart\PolycartServiceProvider;

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

it('registers with the shared runtime, authorizing through the Gate by default', function (): void {
    $package = app(PackageRegistry::class)->get('polycart');

    $config = config('polycart');
    unset($config['authorization']);
    config()->set('polycart', $config);

    expect($package->server)->toBe(PolycartServer::class)
        ->and($package->label)->toBe('Polycart')
        ->and(Authorizer::for($package)->enabled())->toBeTrue();
});
