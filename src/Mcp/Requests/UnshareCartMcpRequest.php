<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\UnshareCartAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class UnshareCartMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('share', $this->cart());
    }

    protected function rules(): array
    {
        return UnshareCartAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
            'member' => ['required', 'string', 'max:26'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $cart = $this->cart();

        /** @var string $id */
        $id = $validated['member'];

        app(UnshareCartAction::class)->execute($cart, $cart->members()->findOrFail($id));

        return Response::structured(['removed' => $id]);
    }
}
