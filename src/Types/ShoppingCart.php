<?php

declare(strict_types=1);

namespace JayI\Polycart\Types;

/**
 * The plain cart a customer fills before checkout.
 *
 * It has no statuses, no conversions, and never expires. Register a subclass
 * under the same key to change any of that.
 */
class ShoppingCart extends CartType
{
    //
}
