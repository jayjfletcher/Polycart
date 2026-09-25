<?php

declare(strict_types=1);

namespace JayI\Polycart\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Turns validated `lines.*` input from the API or MCP into the entries
 * AddLinesAction takes, resolving each purchasable through the allowlist.
 */
final class LineInput
{
    public function __construct(private readonly Morphs $morphs) {}

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, array{purchasable: Model|null, quantity: int, options: array<string, mixed>, meta: array<string, mixed>, unit_price: int|null}>
     */
    public function lines(array $validated): array
    {
        /** @var array<int, array<string, mixed>> $lines */
        $lines = $validated['lines'] ?? [];

        return array_map(function (array $line): array {
            /** @var array<string, mixed> $options */
            $options = $line['options'] ?? [];

            /** @var array<string, mixed> $meta */
            $meta = $line['meta'] ?? [];

            return [
                'purchasable' => $this->morphs->purchasableFrom($line),
                'quantity' => is_numeric($line['quantity'] ?? null) ? (int) $line['quantity'] : 1,
                'options' => $options,
                'meta' => $meta,
                'unit_price' => is_numeric($line['unit_price'] ?? null) ? (int) $line['unit_price'] : null,
            ];
        }, array_values($lines));
    }
}
