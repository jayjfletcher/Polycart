<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Tests\Fixtures\Types;

use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\CartType\Support\CartType;
use RefactorCircus\Polycart\Tests\Fixtures\Models\Quote;

final class QuoteCart extends CartType
{
    public function model(): string
    {
        return Quote::class;
    }

    public function statuses(): string
    {
        return QuoteStatus::class;
    }

    public function transitions(): array
    {
        return [
            QuoteStatus::Draft->value => [QuoteStatus::Submitted],
            QuoteStatus::Submitted->value => [QuoteStatus::Accepted, QuoteStatus::Declined],
        ];
    }

    public function requiresPrice(): bool
    {
        return true;
    }

    public function convertsTo(): array
    {
        return ['order'];
    }

    public function convertedFrom(CartModel $cart, CartModel $source): void
    {
        $cart->meta = array_intersect_key($source->meta ?? [], array_flip(['po_number']));
    }
}
