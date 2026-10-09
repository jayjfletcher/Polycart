<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Polycart\Domains\Cart\Actions\TransitionCartAction;
use RefactorCircus\Polycart\Domains\Cart\Resources\CartResource;

final class TransitionCartRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('transition', $this->cart(), [$this->string('status')->toString()]);
    }

    public function rules(): array
    {
        return TransitionCartAction::rules();
    }

    public function persist(): JsonResponse
    {
        /** @var string $status */
        $status = $this->validated('status');

        $cart = app(TransitionCartAction::class)->execute($this->cart(), $status);

        return (new CartResource($cart))->response();
    }
}
