<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ModelLifecycleEvent;
use JayI\Polycart\Models\CartActivity;

/**
 * The CartActivity `saved` Eloquent event.
 */
final class CartActivitySavedEvent implements ModelLifecycleEvent
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
        return 'saved';
    }
}
