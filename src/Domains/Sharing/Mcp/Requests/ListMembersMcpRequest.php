<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Sharing\Mcp\Requests;

use JayI\Polycart\Domains\Cart\Mcp\Requests\CartRequest;
use JayI\Polycart\Domains\Sharing\Actions\ListMembersAction;
use JayI\Polycart\Domains\Sharing\Models\CartMemberModel;
use JayI\Polycart\Domains\Sharing\Resources\CartMemberResource;
use Laravel\Mcp\ResponseFactory;

final class ListMembersMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('viewAny', CartMemberModel::class, [$this->cart()]);
    }

    protected function rules(): array
    {
        return ListMembersAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function respond(array $validated): ResponseFactory
    {
        return $this->structuredCollection(
            CartMemberResource::collection(app(ListMembersAction::class)->execute($this->cart()))->resolve(),
        );
    }
}
