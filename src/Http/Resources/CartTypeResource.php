<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Resources;

use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JayI\Polycart\Types\CartType;

/**
 * What a cart type allows, so a client can offer only the moves that work.
 *
 * @property CartType $resource
 */
final class CartTypeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $type = $this->resource;
        $statuses = $type->statuses();

        return [
            'key' => $type->key(),
            'statuses' => $statuses === null
                ? []
                : array_map(fn (BackedEnum $status): string => (string) $status->value, $statuses::cases()),
            'initial_status' => $type->initialStatus()?->value,
            'transitions' => $type->transitions() === null ? null : array_map(
                fn (array $to): array => array_map(fn (BackedEnum $status): string => (string) $status->value, $to),
                $type->transitions(),
            ),
            'converts_to' => $type->convertsTo(),
            'parents' => $type->parents(),
            'requires_parent' => $type->requiresParent(),
            'requires_price' => $type->requiresPrice(),
            'merges_lines' => $type->mergesLines(),
            'lifetime' => $type->lifetime()?->spec(),
        ];
    }
}
