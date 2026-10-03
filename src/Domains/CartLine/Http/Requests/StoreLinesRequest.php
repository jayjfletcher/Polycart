<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Domains\Cart\Http\Requests\CartRequest;
use JayI\Polycart\Domains\CartLine\Actions\AddLinesAction;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;
use JayI\Polycart\Domains\CartLine\Resources\CartLineResource;
use JayI\Polycart\Domains\CartLine\Support\LineInput;

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
