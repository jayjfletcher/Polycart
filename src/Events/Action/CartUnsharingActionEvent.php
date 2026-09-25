<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Action;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ActionStartingEvent;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartMember;

/**
 * A member is about to be removed from a cart.
 */
final class CartUnsharingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Cart $cart,
        public CartMember $member,
    ) {}
}
