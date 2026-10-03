<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart;

use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->keepMorphAliases([
            'JayI\Polycart\Models\Cart' => CartModel::class,
        ]);

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
