<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Action;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ActionStartingEvent;

/**
 * An owner's active cart is being looked up, or started.
 */
final class ActiveCartResolvingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public string $type,
        public Model|string $owner,
        public ?Model $scope,
    ) {}
}
