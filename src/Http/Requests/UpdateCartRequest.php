<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Actions\UpdateCartAction;
use JayI\Polycart\Http\Resources\CartResource;

final class UpdateCartRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->cart());
    }

    public function rules(): array
    {
        return UpdateCartAction::rules();
    }

    public function persist(): JsonResponse
    {
        $cart = app(UpdateCartAction::class)->execute($this->cart(), $this->validated());

        return (new CartResource($cart))->response();
    }
}
