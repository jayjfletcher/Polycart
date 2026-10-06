<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;

/**
 * A line's quantity or price is about to change.
 */
final class LineUpdatingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CartModel $cart,
        public CartLineModel $line,
        public int $quantity,
        public ?int $unitPrice,
    ) {}
}
