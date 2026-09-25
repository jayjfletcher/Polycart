<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Action;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ActionFinishedEvent;
use JayI\Polycart\Models\Cart;

/**
 * An owner's active cart was found or started.
 */
final class ActiveCartResolvedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Cart $cart,
    ) {}
}
