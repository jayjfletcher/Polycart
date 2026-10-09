<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Tests\Fixtures\Types;

use RefactorCircus\Polycart\Domains\CartType\Support\CartType;

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
