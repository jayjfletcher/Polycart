<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Sharing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ActionFinishedEvent;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\Sharing\Enums\Visibility;

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
