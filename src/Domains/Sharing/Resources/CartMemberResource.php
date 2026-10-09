<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Sharing\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RefactorCircus\Polycart\Domains\Sharing\Models\CartMemberModel;

/**
 * @mixin CartMemberModel
 */
final class CartMemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'member_type' => $this->member_type,
            'member_id' => $this->member_id,
            'role' => $this->role,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
