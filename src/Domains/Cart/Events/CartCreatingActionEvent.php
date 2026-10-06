<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;

/**
 * A cart is about to be created.
 */
final class CartCreatingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public string $type,
        public Model|string|null $owner,
        public array $attributes,
        public ?Model $scope,
    ) {}
}
