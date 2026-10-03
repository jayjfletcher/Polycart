<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Support\Stages;

use Closure;
use JayI\Polycart\Domains\CartLine\Contracts\AddLineStage;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;
use JayI\Polycart\Domains\CartLine\Support\PendingLine;

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
