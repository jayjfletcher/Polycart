<?php

declare(strict_types=1);

namespace JayI\Polycart\Pricing;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Contracts\PriceResolver;
use JayI\Polycart\Contracts\Purchasable;
use JayI\Polycart\Models\Cart;

/**
 * Asks the purchasable for its own price, if it implements Purchasable.
 */
final class PurchasablePriceResolver implements PriceResolver
{
    public function price(Cart $cart, Model $purchasable, array $options): ?int
    {
        return $purchasable instanceof Purchasable
            ? $purchasable->unitPriceFor($cart, $options)
            : null;
    }
}
