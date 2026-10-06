<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Sharing\Events;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\Sharing\Models\CartMemberModel;

/**
 * A cart's members were listed.
 */
final class MembersListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  Collection<int, CartMemberModel>  $members
     */
    public function __construct(
        public CartModel $cart,
        public Collection $members,
    ) {}
}
