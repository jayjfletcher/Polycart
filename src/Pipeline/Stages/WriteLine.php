<?php

declare(strict_types=1);

namespace JayI\Polycart\Pipeline\Stages;

use Closure;
use JayI\Polycart\Contracts\AddLineStage;
use JayI\Polycart\Models\CartLine;
use JayI\Polycart\Pipeline\PendingLine;

/**
 * Save the line. Stages after this one run once it has an id, still inside
 * the transaction, so a rejection there still undoes the write.
 */
final class WriteLine implements AddLineStage
{
    public function handle(PendingLine $line, Closure $next): PendingLine
    {
        if ($line->line instanceof CartLine) {
            $line->line->save();
        }

        return $next($line);
    }
}
