<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Types;

use JayI\Polycart\Domains\CartType\Support\CartType;

final class ProjectCart extends CartType
{
    public function parents(): array
    {
        return [];
    }
}
