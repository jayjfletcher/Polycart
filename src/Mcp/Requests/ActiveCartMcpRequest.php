<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Actions\ActiveCartAction;
use JayI\Polycart\Http\Resources\CartResource;
use JayI\Polycart\Mcp\Request;
use JayI\Polycart\Support\Morphs;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class ActiveCartMcpRequest extends Request
{
    protected function rules(): array
    {
        $rules = ActiveCartAction::rules();

        // Acting as a user, the owner comes from the session, not the call.
        if ($this->actor() !== null) {
            unset($rules['owner_type'], $rules['owner_id'], $rules['session_key']);
        }

        return $rules;
    }

    protected function handle(array $validated): ResponseFactory
    {
        /** @var string $type */
        $type = $validated['type'];

        /** @var Model|string $owner */
        $owner = app(Morphs::class)->ownerFrom($validated);

        $cart = app(ActiveCartAction::class)->execute($type, $owner);

        return Response::structured((new CartResource($cart->load('lines')))->resolve());
    }
}
