<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Polycart\Domains\Cart\Actions\ConvertCartAction;
use RefactorCircus\Polycart\Domains\Cart\Resources\CartResource;

final class ConvertCartRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('convert', $this->cart(), [$this->string('to')->toString()]);
    }

    public function rules(): array
    {
        return ConvertCartAction::rules();
    }

    public function persist(): JsonResponse
    {
        /** @var string $to */
        $to = $this->validated('to');

        $cart = app(ConvertCartAction::class)->execute($this->cart(), $to, $this->boolean('copy', true));

        return (new CartResource($cart->load('lines')))->response()->setStatusCode(201);
    }
}
