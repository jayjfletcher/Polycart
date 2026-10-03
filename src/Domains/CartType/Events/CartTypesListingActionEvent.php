<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartType\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ActionStartingEvent;

/**
 * The cart types are about to be listed.
 */
final class CartTypesListingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    //
}
