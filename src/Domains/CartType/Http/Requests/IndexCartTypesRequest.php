<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartType\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartType\Actions\ListCartTypesAction;
use JayI\Polycart\Domains\CartType\Http\Resources\CartTypeResource;
use JayI\Polycart\Http\Request;

final class IndexCartTypesRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', CartModel::class);
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
