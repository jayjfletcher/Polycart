<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Mcp\Requests;

use JayI\Polycart\Domains\Cart\Actions\ListCartsAction;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\Cart\Resources\CartResource;
use JayI\Polycart\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

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
