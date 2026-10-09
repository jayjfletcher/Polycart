<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Contracts;

use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;

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
    public function unitPriceFor(CartModel $cart, array $options): ?int;
}
