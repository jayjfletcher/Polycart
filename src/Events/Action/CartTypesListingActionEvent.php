<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Action;

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
