<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Mcp\Requests;

use JayI\Polycart\Domains\Cart\Actions\CreateCartAction;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\Cart\Resources\CartResource;
use JayI\Polycart\Mcp\Request;
use JayI\Polycart\Support\Morphs;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class CreateCartMcpRequest extends Request
{
    protected function authorize(): bool
    {
        if (! parent::authorize() || ! $this->allows('create', CartModel::class)) {
            return false;
        }

        // Nesting under a cart changes that cart's tree.
        $parent = is_string($this->get('parent_id')) ? CartModel::query()->find($this->get('parent_id')) : null;

        return ! $parent instanceof CartModel || $this->allows('update', $parent);
    }

    protected function rules(): array
    {
        return CreateCartAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        /** @var string $type */
        $type = $validated['type'];

        $morphs = app(Morphs::class);

        // Acting as a user, the user is always the owner.
        $owner = $this->actor() ?? $morphs->ownerFrom($validated);

        $cart = app(CreateCartAction::class)->execute($type, $owner, $validated, $morphs->scopeFrom($validated));

        return Response::structured((new CartResource($cart->load('lines')))->resolve());
    }
}
