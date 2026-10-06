<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Mcp\Requests;

use JayI\Polycart\Domains\Cart\Actions\MergeCartsAction;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\Cart\Resources\CartResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class MergeCartsMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        $into = CartModel::query()->find((string) $this->get('into'));

        // Lines leave one cart and land in the other, so both must be editable.
        return $this->allows('update', $this->cart())
            && (! $into instanceof CartModel || $this->allows('update', $into));
    }

    protected function rules(): array
    {
        return MergeCartsAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function respond(array $validated): ResponseFactory
    {
        /** @var string $into */
        $into = $validated['into'];

        $cart = app(MergeCartsAction::class)->execute($this->cart(), CartModel::query()->findOrFail($into));

        return Response::structured((new CartResource($cart->load('lines')))->resolve());
    }
}
