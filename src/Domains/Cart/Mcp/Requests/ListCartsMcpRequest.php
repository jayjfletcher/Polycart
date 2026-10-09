<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Mcp\Requests;

use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Polycart\Domains\Cart\Actions\ListCartsAction;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\Cart\Resources\CartResource;
use RefactorCircus\Polycart\Mcp\Request;

final class ListCartsMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', CartModel::class);
    }

    protected function rules(): array
    {
        return ListCartsAction::rules();
    }

    protected function respond(array $validated): ResponseFactory
    {
        $carts = app(ListCartsAction::class)->execute($validated, $this->actor());

        return $this->structuredCollection(
            CartResource::collection($carts)->resolve(),
            ['next_cursor' => $carts->nextCursor()?->encode()],
        );
    }
}
