<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\RemoveLinesAction;
use JayI\Polycart\Http\Resources\CartResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class RemoveLinesMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->cart());
    }

    protected function rules(): array
    {
        return RemoveLinesAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        /** @var array<int, string> $lines */
        $lines = $validated['lines'];

        $cart = app(RemoveLinesAction::class)->execute($this->cart(), $lines);

        return Response::structured((new CartResource($cart))->resolve());
    }
}
