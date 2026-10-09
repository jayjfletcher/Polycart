<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Support\Stages;

use Closure;
use RefactorCircus\Polycart\Domains\CartLine\Contracts\AddLineStage;
use RefactorCircus\Polycart\Domains\CartLine\Models\CartLineModel;
use RefactorCircus\Polycart\Domains\CartLine\Support\PendingLine;

/**
 * Run the type's own validate(), such as its price requirement.
 */
final class ValidateLine implements AddLineStage
{
    public function handle(PendingLine $line, Closure $next): PendingLine
    {
        if ($line->line instanceof CartLineModel) {
            $line->type->validate($line->cart, $line->line);
        }

        return $next($line);
    }
}
