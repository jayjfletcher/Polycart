<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Types;

use Carbon\CarbonInterval;
use JayI\Polycart\Types\CartType;

final class RetailCart extends CartType
{
    public function lifetime(): CarbonInterval
    {
        return CarbonInterval::days(45);
    }

    public function convertsTo(): array
    {
        return ['saved', 'quote', 'order'];
    }
}
