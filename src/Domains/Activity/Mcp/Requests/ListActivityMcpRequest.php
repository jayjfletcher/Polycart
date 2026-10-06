<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Activity\Mcp\Requests;

use JayI\Polycart\Domains\Activity\Actions\ListActivityAction;
use JayI\Polycart\Domains\Activity\Models\CartActivityModel;
use JayI\Polycart\Domains\Activity\Resources\CartActivityResource;
use JayI\Polycart\Domains\Cart\Mcp\Requests\CartRequest;
use Laravel\Mcp\ResponseFactory;

final class ListActivityMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('viewAny', CartActivityModel::class, [$this->cart()]);
    }

    protected function rules(): array
    {
        return ListActivityAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function respond(array $validated): ResponseFactory
    {
        $cart = $this->cart();
        $activity = app(ListActivityAction::class)->execute($cart, $validated);

        return $this->structuredCollection(
            CartActivityResource::collection($activity)->resolve(),
            ['sources' => $cart->sources ?? [], 'next_cursor' => $activity->nextCursor()?->encode()],
        );
    }
}
