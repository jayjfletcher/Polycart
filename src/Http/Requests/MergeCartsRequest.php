<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Actions\MergeCartsAction;
use JayI\Polycart\Http\Resources\CartResource;
use JayI\Polycart\Models\Cart;

final class MergeCartsRequest extends CartRequest
{
    public function authorize(): bool
    {
        $into = Cart::query()->find($this->string('into')->toString());

        // Lines leave one cart and land in the other, so both must be editable.
        return $this->allows('update', $this->cart())
            && (! $into instanceof Cart || $this->allows('update', $into));
    }

    public function rules(): array
    {
        return MergeCartsAction::rules();
    }

    public function persist(): JsonResponse
    {
        /** @var string $into */
        $into = $this->validated('into');

        $cart = app(MergeCartsAction::class)->execute($this->cart(), Cart::query()->findOrFail($into));

        return (new CartResource($cart->load('lines')))->response();
    }
}
