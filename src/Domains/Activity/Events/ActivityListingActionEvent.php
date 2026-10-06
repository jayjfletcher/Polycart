<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Activity\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;

/**
 * A cart's activity log is about to be read.
 */
final class ActivityListingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public CartModel $cart,
        public array $filters,
    ) {}
}
