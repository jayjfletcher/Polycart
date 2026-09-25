<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Actions\ListCartTypesAction;
use JayI\Polycart\Http\Request;
use JayI\Polycart\Http\Resources\CartTypeResource;

final class IndexCartTypesRequest extends Request
{
    public function rules(): array
    {
        return ListCartTypesAction::rules();
    }

    public function persist(): JsonResponse
    {
        return CartTypeResource::collection(app(ListCartTypesAction::class)->execute())->response();
    }
}
