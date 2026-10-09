<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ModelLifecycleEvent;
use RefactorCircus\Polycart\Domains\CartLine\Models\CartLineModel;

/**
 * The CartLineModel `updating` Eloquent event.
 */
final class CartLineUpdatingEvent implements ModelLifecycleEvent
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
        return 'updating';
    }
}
