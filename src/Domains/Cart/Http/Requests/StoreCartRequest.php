<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Http\Requests\Request;
use RefactorCircus\Polycart\Domains\Cart\Actions\CreateCartAction;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\Cart\Resources\CartResource;
use RefactorCircus\Polycart\Support\Morphs;

final class StoreCartRequest extends Request
{
    public function authorize(): bool
    {
        if (! parent::authorize() || ! $this->allows('create', CartModel::class)) {
            return false;
        }

        // Nesting under a cart changes that cart's tree.
        $parent = $this->filled('parent_id') ? CartModel::query()->find($this->string('parent_id')->toString()) : null;

        return ! $parent instanceof CartModel || $this->allows('update', $parent);
    }

    public function rules(): array
    {
        return CreateCartAction::rules();
    }

    public function persist(): JsonResponse
    {
        $data = $this->validated();

        /** @var string $type */
        $type = $data['type'];

        $morphs = app(Morphs::class);

        // Acting as a user, the user is always the owner.
        $owner = $this->actor() ?? $morphs->ownerFrom($data);

        $cart = app(CreateCartAction::class)->execute($type, $owner, $data, $morphs->scopeFrom($data));

        return (new CartResource($cart->load('lines')))->response()->setStatusCode(201);
    }
}
