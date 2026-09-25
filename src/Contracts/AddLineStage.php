<?php

declare(strict_types=1);

namespace JayI\Polycart\Contracts;

use Closure;
use JayI\Polycart\Pipeline\PendingLine;

/**
 * One step a line goes through on its way into a cart.
 *
 * Do the step, then pass the line on with `$next($line)`. To refuse the line,
 * call `$line->reject($message, $reason)`. Implementing this interface is
 * optional — any class with this `handle()` method, or a closure, works —
 * but it documents the shape.
 */
interface AddLineStage
{
    /**
     * @param  Closure(PendingLine): PendingLine  $next
     */
    public function handle(PendingLine $line, Closure $next): PendingLine;
}
