<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Polycart\Domains\Cart\Http\Requests\CartRequest;
use RefactorCircus\Polycart\Domains\CartLine\Actions\AddLinesAction;
use RefactorCircus\Polycart\Domains\CartLine\Models\CartLineModel;
use RefactorCircus\Polycart\Domains\CartLine\Resources\CartLineResource;
use RefactorCircus\Polycart\Domains\CartLine\Support\LineInput;

final class StoreLinesRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('create', CartLineModel::class, [$this->cart()]);
    }

    public function rules(): array
    {
        return AddLinesAction::rules();
    }

    public function persist(): JsonResponse
    {
        $lines = app(AddLinesAction::class)->execute($this->cart(), app(LineInput::class)->lines($this->validated()));

        return CartLineResource::collection($lines)->response()->setStatusCode(201);
    }
}
