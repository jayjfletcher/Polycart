<?php

declare(strict_types=1);

namespace JayI\Polycart\Pipeline\Stages;

use Closure;
use JayI\Polycart\Contracts\AddLineStage;
use JayI\Polycart\Models\CartLine;
use JayI\Polycart\Pipeline\PendingLine;

/**
 * Run the type's own validate(), such as its price requirement.
 */
final class ValidateLine implements AddLineStage
{
    public function handle(PendingLine $line, Closure $next): PendingLine
    {
        if ($line->line instanceof CartLine) {
            $line->type->validate($line->cart, $line->line);
        }

        return $next($line);
    }
}
