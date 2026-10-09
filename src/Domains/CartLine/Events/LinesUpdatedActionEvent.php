<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Audit\Contracts\Auditable;
use RefactorCircus\Keystone\Contracts\ActionFinishedEvent;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;

/**
 * A batch of lines changed.
 */
final class LinesUpdatedActionEvent implements ActionFinishedEvent, Auditable
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CartModel $cart,
    ) {}

    /**
     * The entry is about the cart the lines belong to.
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
        return [];
    }
}
