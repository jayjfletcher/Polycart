<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Tests\Fixtures\Types;

use RefactorCircus\Polycart\Domains\CartType\Support\CartType;

final class ProjectCart extends CartType
{
    public function parents(): array
    {
        return [];
    }
}
