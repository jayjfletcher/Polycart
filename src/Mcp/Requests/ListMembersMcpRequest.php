<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\ListMembersAction;
use JayI\Polycart\Http\Resources\CartMemberResource;
use JayI\Polycart\Models\CartMember;
use Laravel\Mcp\ResponseFactory;

final class ListMembersMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('viewAny', CartMember::class, [$this->cart()]);
    }

    protected function rules(): array
    {
        return ListMembersAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        return $this->structuredCollection(
            CartMemberResource::collection(app(ListMembersAction::class)->execute($this->cart()))->resolve(),
        );
    }
}
