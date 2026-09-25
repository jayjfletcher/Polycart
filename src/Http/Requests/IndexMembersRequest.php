<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Actions\ListMembersAction;
use JayI\Polycart\Http\Resources\CartMemberResource;
use JayI\Polycart\Models\CartMember;

final class IndexMembersRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('viewAny', CartMember::class, [$this->cart()]);
    }

    public function rules(): array
    {
        return ListMembersAction::rules();
    }

    public function persist(): JsonResponse
    {
        return CartMemberResource::collection(app(ListMembersAction::class)->execute($this->cart()))->response();
    }
}
