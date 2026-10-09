<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Mcp\Requests;

use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Polycart\Domains\Cart\Mcp\Requests\CartRequest;
use RefactorCircus\Polycart\Domains\CartLine\Actions\AddLinesAction;
use RefactorCircus\Polycart\Domains\CartLine\Models\CartLineModel;
use RefactorCircus\Polycart\Domains\CartLine\Resources\CartLineResource;
use RefactorCircus\Polycart\Domains\CartLine\Support\LineInput;

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

    protected function respond(array $validated): ResponseFactory
    {
        $lines = app(AddLinesAction::class)->execute($this->cart(), app(LineInput::class)->lines($validated));

        return $this->structuredCollection(CartLineResource::collection($lines)->resolve());
    }
}
