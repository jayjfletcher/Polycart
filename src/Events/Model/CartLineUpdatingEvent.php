<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ModelLifecycleEvent;
use JayI\Polycart\Models\CartLine;

/**
 * The CartLine `updating` Eloquent event.
 */
final class CartLineUpdatingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public CartLine $line) {}

    public function model(): Model
    {
        return $this->line;
    }

    public function hook(): string
    {
        return 'updating';
    }
}
