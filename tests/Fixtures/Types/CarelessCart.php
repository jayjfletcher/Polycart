<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Types;

use Closure;
use JayI\Polycart\Domains\CartLine\Support\PendingLine;
use JayI\Polycart\Domains\CartLine\Support\Stages\PrepareLine;
use JayI\Polycart\Domains\CartType\Support\CartType;

/**
 * A stage list with a stage that stops without rejecting or writing.
 */
final class CarelessCart extends CartType
{
    public function addLineStages(): array
    {
        return [
            PrepareLine::class,
            fn (PendingLine $line, Closure $next): PendingLine => $line,
        ];
    }
}
