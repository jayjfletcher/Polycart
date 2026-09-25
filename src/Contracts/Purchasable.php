<?php

declare(strict_types=1);

namespace JayI\Polycart\Contracts;

use JayI\Polycart\Models\Cart;

/**
 * A model that can be put in a cart and knows its own price.
 *
 * Implementing it is optional: any model can be added to a cart. The default
 * PriceResolver asks a Purchasable for its price and leaves anything else
 * unpriced.
 */
interface Purchasable
{
    /**
     * The unit price, in minor units, for this cart and these options.
     *
     * Return null when there is no price, such as an item priced on request.
     *
     * @param  array<string, mixed>  $options
     */
    public function unitPriceFor(Cart $cart, array $options): ?int;
}
