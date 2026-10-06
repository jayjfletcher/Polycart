<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Scope\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Polycart\Domains\Scope\Models\CartPathModel;

/**
 * The CartPathModel `updating` Eloquent event.
 */
final class CartPathUpdatingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public CartPathModel $path) {}

    public function model(): Model
    {
        return $this->path;
    }

    public function hook(): string
    {
        return 'updating';
    }
}
