<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Action;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ActionFinishedEvent;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartLine;

/**
 * A line was added, or merged into a matching line when `merged` is true.
 */
final class LineAddedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Cart $cart,
        public CartLine $line,
        public bool $merged,
    ) {}
}
