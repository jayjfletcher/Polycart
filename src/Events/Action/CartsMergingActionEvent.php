<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Action;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ActionStartingEvent;
use JayI\Polycart\Models\Cart;

/**
 * One cart's lines are about to be merged into another.
 */
final class CartsMergingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Cart $from,
        public Cart $into,
    ) {}
}
