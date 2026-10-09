<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Services;

use Illuminate\Database\Eloquent\Model;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\CartLine\Contracts\PriceResolver;
use RefactorCircus\Polycart\Domains\CartLine\Contracts\Purchasable;

/**
 * Asks the purchasable for its own price, if it implements Purchasable.
 */
final class PurchasablePriceResolver implements PriceResolver
{
    public function price(CartModel $cart, Model $purchasable, array $options): ?int
    {
        return $purchasable instanceof Purchasable
            ? $purchasable->unitPriceFor($cart, $options)
            : null;
    }
}
