<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\ConvertCartAction;
use JayI\Polycart\Http\Resources\CartResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class ConvertCartMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('convert', $this->cart(), [(string) $this->get('to')]);
    }

    protected function rules(): array
    {
        return ConvertCartAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        /** @var string $to */
        $to = $validated['to'];

        $cart = app(ConvertCartAction::class)->execute($this->cart(), $to, (bool) ($validated['copy'] ?? true));

        return Response::structured((new CartResource($cart->load('lines')))->resolve());
    }
}
