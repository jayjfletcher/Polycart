<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\SetVisibilityAction;
use JayI\Polycart\Http\Resources\CartResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class SetVisibilityMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('share', $this->cart());
    }

    protected function rules(): array
    {
        return SetVisibilityAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        /** @var string $visibility */
        $visibility = $validated['visibility'];

        $cart = app(SetVisibilityAction::class)->execute($this->cart(), $visibility);

        return Response::structured((new CartResource($cart))->resolve());
    }
}
