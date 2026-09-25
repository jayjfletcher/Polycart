<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Stages;

use Closure;
use JayI\Polycart\Contracts\AddLineStage;
use JayI\Polycart\Pipeline\PendingLine;
use JayI\Polycart\Tests\Fixtures\Models\Person;

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
