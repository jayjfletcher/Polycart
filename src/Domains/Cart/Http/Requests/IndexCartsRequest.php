<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Polycart\Domains\Cart\Actions\ListCartsAction;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\Cart\Resources\CartResource;

final class IndexCartsRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', CartModel::class);
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
