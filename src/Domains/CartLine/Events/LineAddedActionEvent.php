<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;

/**
 * A line was added, or merged into a matching line when `merged` is true.
 */
final class LineAddedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CartModel $cart,
        public CartLineModel $line,
        public bool $merged,
    ) {}
}
