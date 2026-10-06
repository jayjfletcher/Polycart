<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;

/**
 * A batch of lines changed.
 */
final class LinesUpdatedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CartModel $cart,
    ) {}
}
