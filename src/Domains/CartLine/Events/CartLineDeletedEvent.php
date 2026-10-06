<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;

/**
 * The CartLineModel `deleted` Eloquent event.
 */
final class CartLineDeletedEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public CartLineModel $line) {}

    public function model(): Model
    {
        return $this->line;
    }

    public function hook(): string
    {
        return 'deleted';
    }
}
