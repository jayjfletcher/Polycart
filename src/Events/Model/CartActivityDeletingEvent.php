<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ModelLifecycleEvent;
use JayI\Polycart\Models\CartActivity;

/**
 * The CartActivity `deleting` Eloquent event.
 */
final class CartActivityDeletingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public CartActivity $activity) {}

    public function model(): Model
    {
        return $this->activity;
    }

    public function hook(): string
    {
        return 'deleting';
    }
}
