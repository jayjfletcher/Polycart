<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Polycart\Domains\Cart\Actions\ConvertCartAction;
use RefactorCircus\Polycart\Domains\Cart\Resources\CartResource;

final class ConvertCartMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('convert', $this->cart(), [(string) $this->get('to')]);
    }

    protected function rules(): array
    {
        return ConvertCartAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function respond(array $validated): ResponseFactory
    {
        /** @var string $to */
        $to = $validated['to'];

        $cart = app(ConvertCartAction::class)->execute($this->cart(), $to, (bool) ($validated['copy'] ?? true));

        return Response::structured((new CartResource($cart->load('lines')))->resolve());
    }
}
