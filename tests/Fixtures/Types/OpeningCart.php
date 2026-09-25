<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Types;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Tests\Fixtures\Models\Product;
use JayI\Polycart\Types\CartType;

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
