<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Sharing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Audit\Contracts\Auditable;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\Sharing\Models\CartMemberModel;

/**
 * A cart was shared, or a member's role changed.
 */
final class CartSharedActionEvent implements ActionFinishedEvent, Auditable
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CartModel $cart,
        public CartMemberModel $member,
    ) {}

    /**
     * The entry is about the cart that was shared.
     */
    public function auditSubject(): CartModel
    {
        return $this->cart;
    }

    /**
     * @return array<string, mixed>
     */
    public function auditContext(): array
    {
        return [
            'member_type' => $this->member->member_type,
            'member_id' => $this->member->member_id,
            'role' => $this->member->role,
        ];
    }
}
