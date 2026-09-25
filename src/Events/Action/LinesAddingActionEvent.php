<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Action;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ActionStartingEvent;
use JayI\Polycart\Models\Cart;

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
        public Cart $cart,
        public array $lines,
    ) {}
}
