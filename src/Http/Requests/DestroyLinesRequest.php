<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Actions\RemoveLinesAction;
use JayI\Polycart\Http\Resources\CartResource;

/**
 * The ids may come in the body or the query string, since some clients and
 * proxies drop the body of a DELETE.
 */
final class DestroyLinesRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->cart());
    }

    public function rules(): array
    {
        return RemoveLinesAction::rules();
    }

    public function persist(): JsonResponse
    {
        /** @var array<int, string> $lines */
        $lines = $this->validated('lines');

        $cart = app(RemoveLinesAction::class)->execute($this->cart(), $lines);

        return (new CartResource($cart))->response();
    }
}
