<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Sharing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Audit\Contracts\Auditable;
use RefactorCircus\Keystone\Contracts\ActionFinishedEvent;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;

/**
 * A member was removed from a cart.
 */
final class CartUnsharedActionEvent implements ActionFinishedEvent, Auditable
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CartModel $cart,
        public string $memberType,
        public string $memberId,
    ) {}

    /**
     * The entry is about the cart that was unshared.
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
            'member_type' => $this->memberType,
            'member_id' => $this->memberId,
        ];
    }
}
