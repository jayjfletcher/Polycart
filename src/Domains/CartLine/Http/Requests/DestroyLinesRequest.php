<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Domains\Cart\Http\Requests\CartRequest;
use JayI\Polycart\Domains\Cart\Resources\CartResource;
use JayI\Polycart\Domains\CartLine\Actions\RemoveLinesAction;

/**
 * The ids may come in the body or the query string, since some clients and
 * proxies drop the body of a DELETE.
 */
final class DestroyLinesRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allowsEach('delete', $this->linesNamed($this->input('lines')));
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
