<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Activity\Events;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ActionFinishedEvent;
use JayI\Polycart\Domains\Activity\Models\CartActivityModel;
use JayI\Polycart\Domains\Cart\Models\CartModel;

/**
 * A cart's activity log was read.
 */
final class ActivityListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  CursorPaginator<int, CartActivityModel>  $activity
     */
    public function __construct(
        public CartModel $cart,
        public CursorPaginator $activity,
    ) {}
}
