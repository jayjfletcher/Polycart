<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Sharing\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Polycart\Domains\Sharing\Models\CartMemberModel;

/**
 * The CartMemberModel `replicating` Eloquent event.
 */
final class CartMemberReplicatingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public CartMemberModel $member) {}

    public function model(): Model
    {
        return $this->member;
    }

    public function hook(): string
    {
        return 'replicating';
    }
}
