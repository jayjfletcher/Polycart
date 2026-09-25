<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Actions\ShowCartAction;
use JayI\Polycart\Http\Resources\CartResource;

final class ShowCartRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('view', $this->cart());
    }

    public function rules(): array
    {
        return ShowCartAction::rules();
    }

    public function persist(): JsonResponse
    {
        return (new CartResource(app(ShowCartAction::class)->execute($this->cart())))->response();
    }
}
