<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Action;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ActionStartingEvent;
use JayI\Polycart\Models\Cart;

/**
 * A cart is about to be converted into another type.
 */
final class CartConvertingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Cart $cart,
        public string $to,
        public bool $copy,
    ) {}
}
