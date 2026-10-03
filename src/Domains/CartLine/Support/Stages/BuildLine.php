<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Support\Stages;

use Closure;
use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Domains\CartLine\Contracts\AddLineStage;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;
use JayI\Polycart\Domains\CartLine\Support\PendingLine;

/**
 * Build the CartLineModel that will be written, without saving it.
 *
 * Stages after this one see the final quantity and price on `$line->line`,
 * which is the place to check stock or credit limits.
 */
final class BuildLine implements AddLineStage
{
    public function handle(PendingLine $line, Closure $next): PendingLine
    {
        if ($line->existing instanceof CartLineModel) {
            $line->existing->quantity += $line->quantity;
            $line->existing->unit_price = $line->unitPrice ?? $line->existing->unit_price;
            $line->line = $line->existing;

            return $next($line);
        }

        $built = new CartLineModel([
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
