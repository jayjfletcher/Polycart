<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Types;

use JayI\Polycart\Types\CartType;

final class SavedCart extends CartType
{
    public function shareable(): bool
    {
        return false;
    }

    public function convertsTo(): array
    {
        return ['cart'];
    }
}
