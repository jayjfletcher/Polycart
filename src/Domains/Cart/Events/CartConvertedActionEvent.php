<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Audit\Contracts\Auditable;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;

/**
 * A cart was converted. When it was retyped in place, `source` and `cart` are the same cart.
 */
final class CartConvertedActionEvent implements ActionFinishedEvent, Auditable
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CartModel $source,
        public CartModel $cart,
        public string $from,
        public string $to,
        public bool $copy,
    ) {}

    /**
     * The entry is about the converted cart: the copy, or the cart retyped in place.
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
            'from' => $this->from,
            'to' => $this->to,
            'copy' => $this->copy,
            ...($this->copy ? ['converted_from' => $this->source->id] : []),
        ];
    }
}
