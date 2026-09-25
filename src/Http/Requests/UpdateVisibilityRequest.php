<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Actions\SetVisibilityAction;
use JayI\Polycart\Http\Resources\CartResource;

final class UpdateVisibilityRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('share', $this->cart());
    }

    public function rules(): array
    {
        return SetVisibilityAction::rules();
    }

    public function persist(): JsonResponse
    {
        $cart = app(SetVisibilityAction::class)->execute($this->cart(), $this->string('visibility')->toString());

        return (new CartResource($cart))->response();
    }
}
