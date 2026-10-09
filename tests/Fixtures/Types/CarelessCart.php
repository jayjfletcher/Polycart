<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Tests\Fixtures\Types;

use Closure;
use RefactorCircus\Polycart\Domains\CartLine\Support\PendingLine;
use RefactorCircus\Polycart\Domains\CartLine\Support\Stages\PrepareLine;
use RefactorCircus\Polycart\Domains\CartType\Support\CartType;

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
