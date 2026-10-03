<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Support\Stages;

use Closure;
use JayI\Polycart\Domains\CartLine\Contracts\AddLineStage;
use JayI\Polycart\Domains\CartLine\Exceptions\LineRejectedException;
use JayI\Polycart\Domains\CartLine\Support\PendingLine;

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
