<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Events;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Audit\Contracts\Auditable;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;

/**
 * A batch of lines was added.
 */
final class LinesAddedActionEvent implements ActionFinishedEvent, Auditable
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  Collection<int, CartLineModel>  $lines
     */
    public function __construct(
        public CartModel $cart,
        public Collection $lines,
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
        return [
            'lines' => $this->lines->map(fn (CartLineModel $line): array => ['line' => $line->id, 'quantity' => $line->quantity])->values()->all(),
        ];
    }
}
