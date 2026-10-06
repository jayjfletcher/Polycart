<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Audit\Contracts\Auditable;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;

/**
 * A line's quantity or price changed. `line` is null when a quantity of zero removed it.
 */
final class LineUpdatedActionEvent implements ActionFinishedEvent, Auditable
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CartModel $cart,
        public ?CartLineModel $line,
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
        return $this->line === null ? ['removed' => true] : [
            'line' => $this->line->id,
            'quantity' => $this->line->quantity,
            'unit_price' => $this->line->unit_price,
        ];
    }
}
