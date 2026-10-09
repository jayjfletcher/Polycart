<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Tests\Fixtures\Types;

use Carbon\CarbonInterval;
use RefactorCircus\Polycart\Domains\CartType\Support\CartType;

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
