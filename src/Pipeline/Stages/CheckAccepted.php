<?php

declare(strict_types=1);

namespace JayI\Polycart\Pipeline\Stages;

use Closure;
use JayI\Polycart\Contracts\AddLineStage;
use JayI\Polycart\Exceptions\LineRejectedException;
use JayI\Polycart\Pipeline\PendingLine;

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
