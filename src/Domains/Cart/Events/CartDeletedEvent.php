<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;

/**
 * The CartModel `deleted` Eloquent event.
 */
final class CartDeletedEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public CartModel $cart) {}

    public function model(): Model
    {
        return $this->cart;
    }

    public function hook(): string
    {
        return 'deleted';
    }
}
