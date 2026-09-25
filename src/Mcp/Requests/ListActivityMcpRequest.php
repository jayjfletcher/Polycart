<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\ListActivityAction;
use JayI\Polycart\Http\Resources\CartActivityResource;
use Laravel\Mcp\ResponseFactory;

final class ListActivityMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->cart());
    }

    protected function rules(): array
    {
        return ListActivityAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $cart = $this->cart();
        $activity = app(ListActivityAction::class)->execute($cart, $validated);

        return $this->structuredCollection(
            CartActivityResource::collection($activity)->resolve(),
            ['sources' => $cart->sources ?? [], 'next_cursor' => $activity->nextCursor()?->encode()],
        );
    }
}
