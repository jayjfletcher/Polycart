<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Http\Requests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Polycart\Domains\Cart\Actions\ActiveCartAction;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\Cart\Resources\CartResource;
use RefactorCircus\Polycart\Support\Morphs;

final class ActiveCartRequest extends Request
{
    public function authorize(): bool
    {
        // Resolving the active cart starts one when there is none.
        return parent::authorize() && $this->allows('create', CartModel::class);
    }

    public function rules(): array
    {
        $rules = ActiveCartAction::rules();

        // Acting as a user, the owner comes from the session, not the body.
        if ($this->actor() !== null) {
            unset($rules['owner_type'], $rules['owner_id'], $rules['session_key']);
        }

        return $rules;
    }

    public function persist(): JsonResponse
    {
        $data = $this->validated();

        /** @var string $type */
        $type = $data['type'];

        /** @var Model|string $owner */
        $owner = app(Morphs::class)->ownerFrom($data);

        $cart = app(ActiveCartAction::class)->execute($type, $owner);

        return (new CartResource($cart->load('lines')))->response();
    }
}
