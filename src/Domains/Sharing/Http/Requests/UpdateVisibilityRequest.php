<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Sharing\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Polycart\Domains\Cart\Http\Requests\CartRequest;
use RefactorCircus\Polycart\Domains\Cart\Resources\CartResource;
use RefactorCircus\Polycart\Domains\Sharing\Actions\SetVisibilityAction;

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
