<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;

/**
 * An owner's active cart was found or started.
 */
final class ActiveCartResolvedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CartModel $cart,
    ) {}
}
