<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart;

use JayI\Foundation\Audit\AuditHooks;
use JayI\Foundation\Support\ServiceProvider;
use JayI\Polycart\Domains\Cart\Models\CartModel;

class CartServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->keepMorphAliases([
            'JayI\Polycart\Models\Cart' => CartModel::class,
        ]);

        // How the audit log (jayi/keen) names a cart: its label, else its id.
        $this->app->make(AuditHooks::class)
            ->label(CartModel::class, fn (CartModel $cart): string => $cart->label ?? $cart->id);

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
