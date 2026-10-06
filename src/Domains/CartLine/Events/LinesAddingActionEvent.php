<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;

/**
 * A batch of lines is about to be added.
 */
final class LinesAddingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function __construct(
        public CartModel $cart,
        public array $lines,
    ) {}
}
