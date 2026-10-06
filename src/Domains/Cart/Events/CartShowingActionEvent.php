<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;

/**
 * A cart is about to be shown with its lines and members.
 */
final class CartShowingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CartModel $cart,
    ) {}
}
