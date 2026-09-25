<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\MergeCartsAction;
use JayI\Polycart\Http\Resources\CartResource;
use JayI\Polycart\Models\Cart;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class MergeCartsMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        $into = Cart::query()->find((string) $this->get('into'));

        // Lines leave one cart and land in the other, so both must be editable.
        return $this->allows('update', $this->cart())
            && (! $into instanceof Cart || $this->allows('update', $into));
    }

    protected function rules(): array
    {
        return MergeCartsAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        /** @var string $into */
        $into = $validated['into'];

        $cart = app(MergeCartsAction::class)->execute($this->cart(), Cart::query()->findOrFail($into));

        return Response::structured((new CartResource($cart->load('lines')))->resolve());
    }
}
