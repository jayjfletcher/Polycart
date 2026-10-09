<?php

declare(strict_types=1);

namespace Workbench\App\Carts;

use RefactorCircus\Polycart\Domains\CartType\Support\CartType;

/**
 * Things someone wants later, shared read-only with whoever buys them.
 */
class WishlistCart extends CartType
{
    public function roles(): array
    {
        return [
            'owner' => ['*'],
            'viewer' => ['view'],
        ];
    }

    public function convertsTo(): array
    {
        return ['cart'];
    }
}
