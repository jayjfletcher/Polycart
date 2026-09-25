<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Action;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ActionFinishedEvent;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartActivity;

/**
 * A cart's activity log was read.
 */
final class ActivityListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  CursorPaginator<int, CartActivity>  $activity
     */
    public function __construct(
        public Cart $cart,
        public CursorPaginator $activity,
    ) {}
}
