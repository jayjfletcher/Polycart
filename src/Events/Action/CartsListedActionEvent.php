<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Action;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ActionFinishedEvent;
use JayI\Polycart\Models\Cart;

/**
 * Carts were listed.
 */
final class CartsListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  CursorPaginator<int, Cart>  $carts
     */
    public function __construct(
        public CursorPaginator $carts,
        public ?Model $viewer,
    ) {}
}
