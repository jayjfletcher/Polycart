<?php

declare(strict_types=1);

namespace JayI\Polycart\Pipeline\Stages;

use Closure;
use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Contracts\AddLineStage;
use JayI\Polycart\Models\CartLine;
use JayI\Polycart\Pipeline\PendingLine;

/**
 * Build the CartLine that will be written, without saving it.
 *
 * Stages after this one see the final quantity and price on `$line->line`,
 * which is the place to check stock or credit limits.
 */
final class BuildLine implements AddLineStage
{
    public function handle(PendingLine $line, Closure $next): PendingLine
    {
        if ($line->existing instanceof CartLine) {
            $line->existing->quantity += $line->quantity;
            $line->existing->unit_price = $line->unitPrice ?? $line->existing->unit_price;
            $line->line = $line->existing;

            return $next($line);
        }

        $built = new CartLine([
            'purchasable_type' => $line->purchasable?->getMorphClass(),
            'purchasable_id' => $line->purchasable instanceof Model ? (string) $line->purchasable->getKey() : null,
            'fingerprint' => $line->fingerprint,
            'quantity' => $line->quantity,
            'unit_price' => $line->unitPrice,
            'options' => $line->options,
            'meta' => $line->meta,
            'position' => ((int) $line->cart->lines()->max('position')) + 1,
        ]);

        $built->cart()->associate($line->cart);
        $line->line = $built;

        return $next($line);
    }
}
