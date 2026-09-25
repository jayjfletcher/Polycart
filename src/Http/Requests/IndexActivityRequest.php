<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Actions\ListActivityAction;
use JayI\Polycart\Http\Resources\CartActivityResource;
use JayI\Polycart\Models\CartActivity;

final class IndexActivityRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('viewAny', CartActivity::class, [$this->cart()]);
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
