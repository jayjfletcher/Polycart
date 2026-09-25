<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ModelLifecycleEvent;
use JayI\Polycart\Models\Cart;

/**
 * The Cart `trashed` Eloquent event.
 */
final class CartTrashedEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Cart $cart) {}

    public function model(): Model
    {
        return $this->cart;
    }

    public function hook(): string
    {
        return 'trashed';
    }
}
