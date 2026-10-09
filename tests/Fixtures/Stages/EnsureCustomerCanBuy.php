<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Tests\Fixtures\Stages;

use Closure;
use RefactorCircus\Polycart\Domains\CartLine\Contracts\AddLineStage;
use RefactorCircus\Polycart\Domains\CartLine\Support\PendingLine;
use RefactorCircus\Polycart\Tests\Fixtures\Models\Person;

final class EnsureCustomerCanBuy implements AddLineStage
{
    public function handle(PendingLine $line, Closure $next): PendingLine
    {
        if ($line->actor instanceof Person && $line->actor->name === 'Blocked') {
            $line->reject('This customer cannot buy.', 'customer_blocked');
        }

        return $next($line);
    }
}
