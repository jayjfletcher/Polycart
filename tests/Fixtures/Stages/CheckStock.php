<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Stages;

use Closure;
use JayI\Polycart\Contracts\AddLineStage;
use JayI\Polycart\Pipeline\PendingLine;
use JayI\Polycart\Tests\Fixtures\Models\Product;

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
