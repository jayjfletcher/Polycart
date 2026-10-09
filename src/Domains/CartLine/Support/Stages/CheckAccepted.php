<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Support\Stages;

use Closure;
use RefactorCircus\Polycart\Domains\CartLine\Contracts\AddLineStage;
use RefactorCircus\Polycart\Domains\CartLine\Exceptions\LineRejectedException;
use RefactorCircus\Polycart\Domains\CartLine\Support\PendingLine;

/**
 * Refuse what the cart type does not hold.
 */
final class CheckAccepted implements AddLineStage
{
    public function handle(PendingLine $line, Closure $next): PendingLine
    {
        if (! $line->type->accepts($line->purchasable)) {
            throw LineRejectedException::notAccepted($line->type->key(), $line->purchasable);
        }

        return $next($line);
    }
}
