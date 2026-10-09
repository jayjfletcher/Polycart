<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Support\Stages;

use Closure;
use RefactorCircus\Polycart\Domains\CartLine\Contracts\AddLineStage;
use RefactorCircus\Polycart\Domains\CartLine\Support\PendingLine;

/**
 * Price a new line through the type, unless a price was given.
 *
 * A line that merges into an existing one keeps that line's price unless a
 * price was given. Put a discount or surcharge stage after this one.
 */
final class ResolvePrice implements AddLineStage
{
    public function handle(PendingLine $line, Closure $next): PendingLine
    {
        if ($line->unitPrice === null && ! $line->merging()) {
            $line->unitPrice = $line->type->price($line->cart, $line->purchasable, $line->options);
        }

        return $next($line);
    }
}
