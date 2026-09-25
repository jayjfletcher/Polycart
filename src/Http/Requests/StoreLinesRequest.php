<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Actions\AddLinesAction;
use JayI\Polycart\Http\Resources\CartLineResource;
use JayI\Polycart\Support\LineInput;

final class StoreLinesRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->cart());
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
