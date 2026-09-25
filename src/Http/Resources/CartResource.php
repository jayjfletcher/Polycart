<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JayI\Polycart\Models\Cart;

/**
 * @mixin Cart
 */
final class CartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $linesLoaded = $this->resource->relationLoaded('lines');

        return [
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'source' => $this->source,
            'sources' => $this->sources ?? [],
            'label' => $this->label,
            'owner_type' => $this->owner_type,
            'owner_id' => $this->owner_id,
            'session_key' => $this->session_key,
            'scope_type' => $this->scope_type,
            'scope_id' => $this->scope_id,
            'boundary_type' => $this->boundary_type,
            'boundary_id' => $this->boundary_id,
            'visibility' => $this->visibility?->value,
            'parent_id' => $this->parent_id,
            'root_id' => $this->root_id,
            'meta' => $this->meta ?? [],
            'lines_count' => $this->whenCounted('lines'),
            'children_count' => $this->whenCounted('children'),
            'quantity' => $this->when($linesLoaded, fn (): int => $this->resource->quantity()),
            'subtotal' => $this->when($linesLoaded, fn (): int => $this->resource->subtotal()),
            'fully_priced' => $this->when($linesLoaded, fn (): bool => $this->resource->isFullyPriced()),
            'lines' => CartLineResource::collection($this->whenLoaded('lines')),
            'members' => CartMemberResource::collection($this->whenLoaded('members')),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
