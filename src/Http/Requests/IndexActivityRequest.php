<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Actions\ListActivityAction;
use JayI\Polycart\Http\Resources\CartActivityResource;

final class IndexActivityRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('view', $this->cart());
    }

    public function rules(): array
    {
        return ListActivityAction::rules();
    }

    public function persist(): JsonResponse
    {
        $activity = app(ListActivityAction::class)->execute($this->cart(), $this->validated());

        return CartActivityResource::collection($activity)->response();
    }
}
