<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Tests\Fixtures\Types;

use RefactorCircus\Polycart\Domains\CartType\Support\CartType;

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
