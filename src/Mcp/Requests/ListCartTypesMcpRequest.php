<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\ListCartTypesAction;
use JayI\Polycart\Http\Resources\CartTypeResource;
use JayI\Polycart\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

final class ListCartTypesMcpRequest extends Request
{
    protected function rules(): array
    {
        return ListCartTypesAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        return $this->structuredCollection(
            CartTypeResource::collection(app(ListCartTypesAction::class)->execute())->resolve(),
        );
    }
}
