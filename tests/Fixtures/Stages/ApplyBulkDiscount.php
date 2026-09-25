<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Stages;

use Closure;
use JayI\Polycart\Pipeline\PendingLine;

/**
 * Configured from the stage list: `ApplyBulkDiscount::class.':10,20'` takes
 * 20% off lines of 10 or more.
 */
final class ApplyBulkDiscount
{
    public function handle(PendingLine $line, Closure $next, string $minimum, string $percent): PendingLine
    {
        if ($line->unitPrice !== null && $line->resultingQuantity() >= (int) $minimum) {
            $line->unitPrice = intdiv($line->unitPrice * (100 - (int) $percent), 100);
            $line->context['discounted'] = true;
        }

        return $next($line);
    }
}
