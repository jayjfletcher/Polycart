<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Sharing\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Domains\Cart\Http\Requests\CartRequest;
use JayI\Polycart\Domains\Sharing\Actions\ListMembersAction;
use JayI\Polycart\Domains\Sharing\Models\CartMemberModel;
use JayI\Polycart\Domains\Sharing\Resources\CartMemberResource;

final class IndexMembersRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('viewAny', CartMemberModel::class, [$this->cart()]);
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
