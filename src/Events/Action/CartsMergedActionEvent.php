<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Action;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ActionFinishedEvent;
use JayI\Polycart\Models\Cart;

/**
 * One cart's lines were merged into another, and the first was deleted.
 */
final class CartsMergedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Cart $from,
        public Cart $into,
    ) {}
}
