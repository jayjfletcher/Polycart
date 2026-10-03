<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Activity\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Domains\Activity\Actions\ListActivityAction;
use JayI\Polycart\Domains\Activity\Models\CartActivityModel;
use JayI\Polycart\Domains\Activity\Resources\CartActivityResource;
use JayI\Polycart\Domains\Cart\Http\Requests\CartRequest;

final class IndexActivityRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('viewAny', CartActivityModel::class, [$this->cart()]);
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
