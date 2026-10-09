<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Audit\Contracts\Auditable;
use RefactorCircus\Foundation\Contracts\ActionFinishedEvent;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;

/**
 * A batch of lines was removed.
 */
final class LinesRemovedActionEvent implements ActionFinishedEvent, Auditable
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<int, string>  $lineIds
     */
    public function __construct(
        public CartModel $cart,
        public array $lineIds,
    ) {}

    /**
     * The entry is about the cart the lines belonged to.
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
        return ['lines' => $this->lineIds];
    }
}
