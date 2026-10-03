<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartType\Mcp\Requests;

use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartType\Actions\ListCartTypesAction;
use JayI\Polycart\Domains\CartType\Http\Resources\CartTypeResource;
use JayI\Polycart\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

final class ListCartTypesMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', CartModel::class);
    }

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
