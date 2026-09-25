<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\DeleteCartAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class DeleteCartMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('delete', $this->cart());
    }

    protected function rules(): array
    {
        return DeleteCartAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $cart = $this->cart();

        app(DeleteCartAction::class)->execute($cart);

        return Response::structured(['deleted' => $cart->id]);
    }
}
