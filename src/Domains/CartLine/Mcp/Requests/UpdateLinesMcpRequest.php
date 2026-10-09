<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Polycart\Domains\Cart\Mcp\Requests\CartRequest;
use RefactorCircus\Polycart\Domains\Cart\Resources\CartResource;
use RefactorCircus\Polycart\Domains\CartLine\Actions\UpdateLinesAction;

final class UpdateLinesMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allowsEach('update', $this->linesNamed(data_get($this->get('lines'), '*.id')));
    }

    protected function rules(): array
    {
        return UpdateLinesAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function respond(array $validated): ResponseFactory
    {
        /** @var array<int, array{id: string, quantity: int|numeric-string, unit_price?: int|numeric-string|null}> $lines */
        $lines = $validated['lines'];

        $cart = app(UpdateLinesAction::class)->execute($this->cart(), array_map(fn (array $line): array => [
            'id' => $line['id'],
            'quantity' => (int) $line['quantity'],
            'unit_price' => isset($line['unit_price']) ? (int) $line['unit_price'] : null,
        ], $lines));

        return Response::structured((new CartResource($cart))->resolve());
    }
}
