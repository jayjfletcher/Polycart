<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Types;

use JayI\Polycart\Domains\CartType\Support\CartType;

final class SetCart extends CartType
{
    public function parents(): array
    {
        return ['project'];
    }

    public function requiresParent(): bool
    {
        return true;
    }
}
