<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Polycart\Domains\Cart\Actions\ClearCartAction;
use RefactorCircus\Polycart\Domains\Cart\Resources\CartResource;

final class ClearCartMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->cart());
    }

    protected function rules(): array
    {
        return ClearCartAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function respond(array $validated): ResponseFactory
    {
        $cart = app(ClearCartAction::class)->execute($this->cart());

        return Response::structured((new CartResource($cart->load('lines')))->resolve());
    }
}
