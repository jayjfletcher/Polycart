<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Audit\Contracts\Auditable;
use RefactorCircus\Foundation\Contracts\ActionFinishedEvent;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;

/**
 * One cart's lines were merged into another, and the first was deleted.
 */
final class CartsMergedActionEvent implements ActionFinishedEvent, Auditable
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CartModel $from,
        public CartModel $into,
    ) {}

    /**
     * The entry is about the cart the lines were merged into, so its history shows where they came from.
     */
    public function auditSubject(): CartModel
    {
        return $this->into;
    }

    /**
     * @return array<string, mixed>
     */
    public function auditContext(): array
    {
        return ['merged_from' => $this->from->id];
    }
}
