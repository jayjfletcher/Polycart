<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Polycart\Domains\Cart\Actions\TransitionCartAction;
use RefactorCircus\Polycart\Domains\Cart\Resources\CartResource;

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

    protected function respond(array $validated): ResponseFactory
    {
        /** @var string $status */
        $status = $validated['status'];

        $cart = app(TransitionCartAction::class)->execute($this->cart(), $status);

        return Response::structured((new CartResource($cart))->resolve());
    }
}
