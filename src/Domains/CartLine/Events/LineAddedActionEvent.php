<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Audit\Contracts\Auditable;
use RefactorCircus\Keystone\Contracts\ActionFinishedEvent;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\CartLine\Models\CartLineModel;

/**
 * A line was added, or merged into a matching line when `merged` is true.
 */
final class LineAddedActionEvent implements ActionFinishedEvent, Auditable
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CartModel $cart,
        public CartLineModel $line,
        public bool $merged,
    ) {}

    /**
     * The entry is about the cart the line belongs to.
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
            'line' => $this->line->id,
            'purchasable_type' => $this->line->purchasable_type,
            'purchasable_id' => $this->line->purchasable_id,
            'quantity' => $this->line->quantity,
            'merged' => $this->merged,
        ];
    }
}
