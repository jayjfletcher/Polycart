<?php

declare(strict_types=1);

namespace JayI\Polycart\Contracts;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Models\Cart;

/**
 * Prices a purchasable when it is added to a cart without an explicit price.
 *
 * Bind your own to price from an ERP, a price book, or customer-specific
 * contract pricing. A cart type can still override pricing for its own carts
 * through CartType::price().
 */
interface PriceResolver
{
    /**
     * The unit price in minor units, or null when there is none.
     *
     * @param  array<string, mixed>  $options
     */
    public function price(Cart $cart, Model $purchasable, array $options): ?int;
}
