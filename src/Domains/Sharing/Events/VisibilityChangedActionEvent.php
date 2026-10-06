<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Sharing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\Sharing\Enums\Visibility;

/**
 * A cart's visibility changed.
 */
final class VisibilityChangedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CartModel $cart,
        public Visibility $from,
        public Visibility $to,
    ) {}
}
