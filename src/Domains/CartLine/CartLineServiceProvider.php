<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine;

use JayI\Foundation\Audit\AuditHooks;
use JayI\Foundation\Support\ServiceProvider;
use JayI\Polycart\Domains\CartLine\Contracts\PriceResolver;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;
use JayI\Polycart\Domains\CartLine\Services\PurchasablePriceResolver;

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

        // How the audit log (jayi/keen) names a line: a custom line by its
        // description, any other by what it is for.
        $this->app->make(AuditHooks::class)->label(CartLineModel::class, fn (CartLineModel $line): string => match (true) {
            ! $line->isCustom() => class_basename((string) $line->purchasable_type).' #'.$line->purchasable_id,
            is_string($line->meta['description'] ?? null) => $line->meta['description'],
            default => $line->id,
        });

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
