<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Types;

use JayI\Polycart\Models\Cart;
use JayI\Polycart\Tests\Fixtures\Models\Quote;
use JayI\Polycart\Types\CartType;

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

    public function convertedFrom(Cart $cart, Cart $source): void
    {
        $cart->meta = array_intersect_key($source->meta ?? [], array_flip(['po_number']));
    }
}
