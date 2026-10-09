<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Mcp\Requests;

use Illuminate\Database\Eloquent\Model;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Polycart\Domains\Cart\Actions\ActiveCartAction;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\Cart\Resources\CartResource;
use RefactorCircus\Polycart\Mcp\Request;
use RefactorCircus\Polycart\Support\Morphs;

final class ActiveCartMcpRequest extends Request
{
    protected function authorize(): bool
    {
        // Resolving the active cart starts one when there is none.
        return parent::authorize() && $this->allows('create', CartModel::class);
    }

    protected function rules(): array
    {
        $rules = ActiveCartAction::rules();

        // Acting as a user, the owner comes from the session, not the call.
        if ($this->actor() !== null) {
            unset($rules['owner_type'], $rules['owner_id'], $rules['session_key']);
        }

        return $rules;
    }

    protected function respond(array $validated): ResponseFactory
    {
        /** @var string $type */
        $type = $validated['type'];

        /** @var Model|string $owner */
        $owner = app(Morphs::class)->ownerFrom($validated);

        $cart = app(ActiveCartAction::class)->execute($type, $owner);

        return Response::structured((new CartResource($cart->load('lines')))->resolve());
    }
}
