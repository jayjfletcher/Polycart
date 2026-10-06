<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Mcp\Requests;

use JayI\Polycart\Domains\Cart\Mcp\Requests\CartRequest;
use JayI\Polycart\Domains\Cart\Resources\CartResource;
use JayI\Polycart\Domains\CartLine\Actions\RemoveLinesAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

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
