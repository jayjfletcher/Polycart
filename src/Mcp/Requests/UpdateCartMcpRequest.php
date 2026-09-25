<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\UpdateCartAction;
use JayI\Polycart\Http\Resources\CartResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class UpdateCartMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->cart());
    }

    protected function rules(): array
    {
        return UpdateCartAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $cart = app(UpdateCartAction::class)->execute($this->cart(), $validated);

        return Response::structured((new CartResource($cart))->resolve());
    }
}
