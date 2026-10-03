<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Mcp\Requests;

use JayI\Polycart\Domains\Cart\Mcp\Requests\CartRequest;
use JayI\Polycart\Domains\CartLine\Actions\AddLinesAction;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;
use JayI\Polycart\Domains\CartLine\Resources\CartLineResource;
use JayI\Polycart\Domains\CartLine\Support\LineInput;
use Laravel\Mcp\ResponseFactory;

final class AddLinesMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('create', CartLineModel::class, [$this->cart()]);
    }

    protected function rules(): array
    {
        return AddLinesAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $lines = app(AddLinesAction::class)->execute($this->cart(), app(LineInput::class)->lines($validated));

        return $this->structuredCollection(CartLineResource::collection($lines)->resolve());
    }
}
