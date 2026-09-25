<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ModelLifecycleEvent;
use JayI\Polycart\Models\CartPath;

/**
 * The CartPath `replicating` Eloquent event.
 */
final class CartPathReplicatingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public CartPath $path) {}

    public function model(): Model
    {
        return $this->path;
    }

    public function hook(): string
    {
        return 'replicating';
    }
}
