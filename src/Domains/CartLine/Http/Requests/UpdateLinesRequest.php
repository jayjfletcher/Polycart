<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Polycart\Domains\Cart\Http\Requests\CartRequest;
use RefactorCircus\Polycart\Domains\Cart\Resources\CartResource;
use RefactorCircus\Polycart\Domains\CartLine\Actions\UpdateLinesAction;

final class UpdateLinesRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allowsEach('update', $this->linesNamed(data_get($this->input('lines'), '*.id')));
    }

    public function rules(): array
    {
        return UpdateLinesAction::rules();
    }

    public function persist(): JsonResponse
    {
        /** @var array<int, array{id: string, quantity: int, unit_price?: int|null}> $lines */
        $lines = $this->validated('lines');

        $cart = app(UpdateLinesAction::class)->execute($this->cart(), $lines);

        return (new CartResource($cart))->response();
    }
}
