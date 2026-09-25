<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\ShowCartAction;
use JayI\Polycart\Http\Resources\CartResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class ShowCartMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->cart());
    }

    protected function rules(): array
    {
        return ShowCartAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $cart = app(ShowCartAction::class)->execute($this->cart());

        return Response::structured((new CartResource($cart))->resolve());
    }
}
