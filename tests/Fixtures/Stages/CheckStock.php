<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Tests\Fixtures\Stages;

use Closure;
use RefactorCircus\Polycart\Domains\CartLine\Contracts\AddLineStage;
use RefactorCircus\Polycart\Domains\CartLine\Support\PendingLine;
use RefactorCircus\Polycart\Tests\Fixtures\Models\Product;

/**
 * Runs after BuildLine, so it sees the quantity the line will end up with,
 * including what was already in the cart.
 */
final class CheckStock implements AddLineStage
{
    public function handle(PendingLine $line, Closure $next): PendingLine
    {
        $product = $line->purchasable;
        $quantity = $line->line->quantity ?? $line->quantity;

        if ($product instanceof Product && $product->stock !== null && $quantity > $product->stock) {
            $line->reject(sprintf('Only %d left.', $product->stock), 'out_of_stock');
        }

        return $next($line);
    }
}
