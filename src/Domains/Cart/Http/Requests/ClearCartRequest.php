<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Domains\Cart\Actions\ClearCartAction;
use JayI\Polycart\Domains\Cart\Resources\CartResource;

final class ClearCartRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->cart());
    }

    public function rules(): array
    {
        return ClearCartAction::rules();
    }

    public function persist(): JsonResponse
    {
        $cart = app(ClearCartAction::class)->execute($this->cart());

        return (new CartResource($cart->load('lines')))->response();
    }
}
