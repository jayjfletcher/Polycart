<?php

declare(strict_types=1);

namespace JayI\Polycart\Events\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Polycart\Contracts\ModelLifecycleEvent;
use JayI\Polycart\Models\CartMember;

/**
 * The CartMember `deleted` Eloquent event.
 */
final class CartMemberDeletedEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public CartMember $member) {}

    public function model(): Model
    {
        return $this->member;
    }

    public function hook(): string
    {
        return 'deleted';
    }
}
