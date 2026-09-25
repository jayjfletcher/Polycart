<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\UpdateLinesAction;
use JayI\Polycart\Http\Resources\CartResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

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

    protected function handle(array $validated): ResponseFactory
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
