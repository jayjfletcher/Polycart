<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Action;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ActionStartingEvent;
use JayI\Polycart\Models\Cart;

/**
 * A line is about to go through the add pipeline.
 */
final class LineAddingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $options
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public Cart $cart,
        public ?Model $purchasable,
        public int $quantity,
        public array $options,
        public array $meta,
        public ?int $unitPrice,
    ) {}
}
