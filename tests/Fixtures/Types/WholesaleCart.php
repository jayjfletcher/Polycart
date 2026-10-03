<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Types;

use Closure;
use JayI\Polycart\Domains\CartLine\Support\PendingLine;
use JayI\Polycart\Domains\CartLine\Support\Stages\BuildLine;
use JayI\Polycart\Domains\CartLine\Support\Stages\CheckAccepted;
use JayI\Polycart\Domains\CartLine\Support\Stages\PrepareLine;
use JayI\Polycart\Domains\CartLine\Support\Stages\ResolvePrice;
use JayI\Polycart\Domains\CartType\Support\CartType;
use JayI\Polycart\Tests\Fixtures\Stages\ApplyBulkDiscount;
use JayI\Polycart\Tests\Fixtures\Stages\CheckStock;
use JayI\Polycart\Tests\Fixtures\Stages\EnsureCustomerCanBuy;

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
