<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Sharing\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;

/**
 * A cart is about to be shared, or a member's role changed.
 */
final class CartSharingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CartModel $cart,
        public Model $member,
        public string $role,
    ) {}
}
