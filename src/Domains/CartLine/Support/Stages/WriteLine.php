<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Support\Stages;

use Closure;
use JayI\Polycart\Domains\CartLine\Contracts\AddLineStage;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;
use JayI\Polycart\Domains\CartLine\Support\PendingLine;

/**
 * Save the line. Stages after this one run once it has an id, still inside
 * the transaction, so a rejection there still undoes the write.
 */
final class WriteLine implements AddLineStage
{
    public function handle(PendingLine $line, Closure $next): PendingLine
    {
        if ($line->line instanceof CartLineModel) {
            $line->line->save();
        }

        return $next($line);
    }
}
