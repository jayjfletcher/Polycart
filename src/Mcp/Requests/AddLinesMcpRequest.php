<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\AddLinesAction;
use JayI\Polycart\Http\Resources\CartLineResource;
use JayI\Polycart\Support\LineInput;
use Laravel\Mcp\ResponseFactory;

final class AddLinesMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->cart());
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
