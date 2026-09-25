<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\TransitionCartAction;
use JayI\Polycart\Http\Resources\CartResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class TransitionCartMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('transition', $this->cart(), [(string) $this->get('status')]);
    }

    protected function rules(): array
    {
        return TransitionCartAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        /** @var string $status */
        $status = $validated['status'];

        $cart = app(TransitionCartAction::class)->execute($this->cart(), $status);

        return Response::structured((new CartResource($cart))->resolve());
    }
}
