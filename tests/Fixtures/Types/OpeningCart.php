<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Tests\Fixtures\Types;

use Illuminate\Database\Eloquent\Model;
use RefactorCircus\Polycart\Domains\CartType\Support\CartType;
use RefactorCircus\Polycart\Tests\Fixtures\Models\Product;

/**
 * Holds only catalogue products, lists every add separately, and keeps only
 * the meta it knows about.
 */
final class OpeningCart extends CartType
{
    public function parents(): array
    {
        return ['set'];
    }

    public function requiresParent(): bool
    {
        return true;
    }

    public function accepts(?Model $purchasable): bool
    {
        return $purchasable instanceof Product;
    }

    public function mergesLines(): bool
    {
        return false;
    }

    public function prepareMeta(array $meta): array
    {
        return array_intersect_key($meta, array_flip(['note']));
    }
}
