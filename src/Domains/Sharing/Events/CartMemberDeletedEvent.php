<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Sharing\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ModelLifecycleEvent;
use RefactorCircus\Polycart\Domains\Sharing\Models\CartMemberModel;

/**
 * The CartMemberModel `deleted` Eloquent event.
 */
final class CartMemberDeletedEvent implements ModelLifecycleEvent
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
        return 'deleted';
    }
}
