<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Support\Stages;

use Closure;
use RefactorCircus\Polycart\Domains\CartLine\Contracts\AddLineStage;
use RefactorCircus\Polycart\Domains\CartLine\Models\CartLineModel;
use RefactorCircus\Polycart\Domains\CartLine\Support\PendingLine;

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
