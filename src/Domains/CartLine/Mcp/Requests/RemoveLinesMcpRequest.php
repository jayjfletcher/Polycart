<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Polycart\Domains\Cart\Mcp\Requests\CartRequest;
use RefactorCircus\Polycart\Domains\Cart\Resources\CartResource;
use RefactorCircus\Polycart\Domains\CartLine\Actions\RemoveLinesAction;

final class RemoveLinesMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allowsEach('delete', $this->linesNamed($this->get('lines')));
    }

    protected function rules(): array
    {
        return RemoveLinesAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function respond(array $validated): ResponseFactory
    {
        /** @var array<int, string> $lines */
        $lines = $validated['lines'];

        $cart = app(RemoveLinesAction::class)->execute($this->cart(), $lines);

        return Response::structured((new CartResource($cart))->resolve());
    }
}
