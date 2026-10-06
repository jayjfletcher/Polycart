<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Sharing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;

/**
 * A cart's members are about to be listed.
 */
final class MembersListingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CartModel $cart,
    ) {}
}
