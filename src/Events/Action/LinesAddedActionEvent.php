<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Action;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ActionFinishedEvent;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartLine;

/**
 * A batch of lines was added.
 */
final class LinesAddedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  Collection<int, CartLine>  $lines
     */
    public function __construct(
        public Cart $cart,
        public Collection $lines,
    ) {}
}
