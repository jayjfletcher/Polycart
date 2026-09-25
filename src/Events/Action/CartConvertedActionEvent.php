<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Action;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ActionFinishedEvent;
use JayI\Polycart\Models\Cart;

/**
 * A cart was converted. When it was retyped in place, `source` and `cart` are the same cart.
 */
final class CartConvertedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Cart $source,
        public Cart $cart,
        public string $from,
        public string $to,
        public bool $copy,
    ) {}
}
