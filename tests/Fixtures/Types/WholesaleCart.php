<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Types;

use Closure;
use JayI\Polycart\Pipeline\PendingLine;
use JayI\Polycart\Pipeline\Stages\BuildLine;
use JayI\Polycart\Pipeline\Stages\CheckAccepted;
use JayI\Polycart\Pipeline\Stages\PrepareLine;
use JayI\Polycart\Pipeline\Stages\ResolvePrice;
use JayI\Polycart\Tests\Fixtures\Stages\ApplyBulkDiscount;
use JayI\Polycart\Tests\Fixtures\Stages\CheckStock;
use JayI\Polycart\Tests\Fixtures\Stages\EnsureCustomerCanBuy;
use JayI\Polycart\Types\CartType;

/**
 * A cart with its own add pipeline: customer rules, bulk pricing, stock.
 */
final class WholesaleCart extends CartType
{
    public function addLineStages(): array
    {
        $stages = parent::addLineStages();

        $stages = self::insertStagesBefore($stages, PrepareLine::class, [
            function (PendingLine $line, Closure $next): PendingLine {
                $line->meta['added_via'] = $line->source;

                return $next($line);
            },
        ]);

        $stages = self::insertStagesAfter($stages, CheckAccepted::class, [EnsureCustomerCanBuy::class]);
        $stages = self::insertStagesAfter($stages, ResolvePrice::class, [ApplyBulkDiscount::class.':10,20']);

        return self::insertStagesAfter($stages, BuildLine::class, [CheckStock::class]);
    }
}
