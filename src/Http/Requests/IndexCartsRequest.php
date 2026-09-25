<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Actions\ListCartsAction;
use JayI\Polycart\Http\Request;
use JayI\Polycart\Http\Resources\CartResource;
use JayI\Polycart\Models\Cart;

final class IndexCartsRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', Cart::class);
    }

    public function rules(): array
    {
        return ListCartsAction::rules();
    }

    public function persist(): JsonResponse
    {
        return CartResource::collection(app(ListCartsAction::class)->execute($this->validated(), $this->actor()))->response();
    }
}
