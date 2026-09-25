<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Actions\ListCartTypesAction;
use JayI\Polycart\Http\Request;
use JayI\Polycart\Http\Resources\CartTypeResource;
use JayI\Polycart\Models\Cart;

final class IndexCartTypesRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', Cart::class);
    }

    public function rules(): array
    {
        return ListCartTypesAction::rules();
    }

    public function persist(): JsonResponse
    {
        return CartTypeResource::collection(app(ListCartTypesAction::class)->execute())->response();
    }
}
