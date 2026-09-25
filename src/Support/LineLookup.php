<?php

declare(strict_types=1);

namespace JayI\Polycart\Support;

use JayI\Polycart\Exceptions\LineRejectedException;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartLine;

/**
 * Finds a line within one cart, so a batch can never reach another cart's.
 *
 * @internal
 */
final class LineLookup
{
    public static function in(Cart $cart, CartLine|string $line): CartLine
    {
        $id = $line instanceof CartLine ? $line->id : $line;

        $found = $cart->lines()->whereKey($id)->lockForUpdate()->first();

        if (! $found instanceof CartLine) {
            throw LineRejectedException::because(sprintf('Line [%s] is not in this cart.', $id), 'line_not_found');
        }

        return $found;
    }
}
