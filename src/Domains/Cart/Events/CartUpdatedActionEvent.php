<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;

/**
 * A cart's label or meta changed.
 */
final class CartUpdatedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<int, string>  $changed
     */
    public function __construct(
        public CartModel $cart,
        public array $changed,
    ) {}
}
