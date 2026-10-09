<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Support;

use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\CartLine\Exceptions\LineRejectedException;
use RefactorCircus\Polycart\Domains\CartLine\Models\CartLineModel;

/**
 * Finds a line within one cart, so a batch can never reach another cart's.
 *
 * @internal
 */
final class LineLookup
{
    public static function in(CartModel $cart, CartLineModel|string $line): CartLineModel
    {
        $id = $line instanceof CartLineModel ? $line->id : $line;

        $found = $cart->lines()->whereKey($id)->lockForUpdate()->first();

        if (! $found instanceof CartLineModel) {
            throw LineRejectedException::because(sprintf('Line [%s] is not in this cart.', $id), 'line_not_found');
        }

        return $found;
    }
}
