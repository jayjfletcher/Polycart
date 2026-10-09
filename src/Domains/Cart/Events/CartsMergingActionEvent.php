<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Audit\Contracts\Auditable;
use RefactorCircus\Keystone\Contracts\ActionStartingEvent;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;

/**
 * One cart's lines are about to be merged into another.
 */
final class CartsMergingActionEvent implements ActionStartingEvent, Auditable
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CartModel $from,
        public CartModel $into,
    ) {}

    /**
     * The cart the lines are merged into, whose state the audit log snapshots.
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
