<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Support\Stages;

use Closure;
use RefactorCircus\Polycart\Domains\CartLine\Contracts\AddLineStage;
use RefactorCircus\Polycart\Domains\CartLine\Exceptions\LineRejectedException;
use RefactorCircus\Polycart\Domains\CartLine\Support\PendingLine;

/**
 * Check the quantity, let the type normalise options and meta, and work out
 * the line's identity.
 */
final class PrepareLine implements AddLineStage
{
    public function handle(PendingLine $line, Closure $next): PendingLine
    {
        if ($line->quantity < 1) {
            throw LineRejectedException::quantity($line->quantity);
        }

        $line->options = $line->type->prepareOptions($line->options);
        $line->meta = $line->type->prepareMeta($line->meta);
        $line->fingerprint = $line->type->fingerprint($line->purchasable, $line->options, $line->meta);

        return $next($line);
    }
}
