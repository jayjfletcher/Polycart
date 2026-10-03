<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartType\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ActionFinishedEvent;
use JayI\Polycart\Domains\CartType\Support\CartType;

/**
 * The cart types were listed.
 */
final class CartTypesListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<int, CartType>  $types
     */
    public function __construct(
        public array $types,
    ) {}
}
