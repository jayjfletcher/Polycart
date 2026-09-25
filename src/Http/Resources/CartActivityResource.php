<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JayI\Polycart\Models\CartActivity;

/**
 * @mixin CartActivity
 */
final class CartActivityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'action' => $this->action,
            'source' => $this->source,
            'actor_type' => $this->actor_type,
            'actor_id' => $this->actor_id,
            'context' => $this->context ?? [],
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
