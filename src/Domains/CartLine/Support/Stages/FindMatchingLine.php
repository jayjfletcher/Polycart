<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Support\Stages;

use Closure;
use JayI\Polycart\Domains\CartLine\Contracts\AddLineStage;
use JayI\Polycart\Domains\CartLine\Support\PendingLine;

/**
 * Find the line this add merges into, locking it for the rest of the add.
 *
 * Skipped when the type keeps every add as its own line.
 */
final class FindMatchingLine implements AddLineStage
{
    public function handle(PendingLine $line, Closure $next): PendingLine
    {
        if ($line->type->mergesLines() && $line->fingerprint !== null) {
            $line->existing = $line->cart->lines()
                ->where('fingerprint', $line->fingerprint)
                ->lockForUpdate()
                ->first();
        }

        return $next($line);
    }
}
