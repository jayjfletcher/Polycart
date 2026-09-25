<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Types;

use Closure;
use JayI\Polycart\Pipeline\PendingLine;
use JayI\Polycart\Pipeline\Stages\PrepareLine;
use JayI\Polycart\Types\CartType;

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
