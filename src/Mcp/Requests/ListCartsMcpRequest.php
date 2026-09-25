<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\ListCartsAction;
use JayI\Polycart\Http\Resources\CartResource;
use JayI\Polycart\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

final class ListCartsMcpRequest extends Request
{
    protected function rules(): array
    {
        return ListCartsAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $carts = app(ListCartsAction::class)->execute($validated, $this->actor());

        return $this->structuredCollection(
            CartResource::collection($carts)->resolve(),
            ['next_cursor' => $carts->nextCursor()?->encode()],
        );
    }
}
