<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\CreateCartAction;
use JayI\Polycart\Http\Resources\CartResource;
use JayI\Polycart\Mcp\Request;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Support\Morphs;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class CreateCartMcpRequest extends Request
{
    protected function authorize(): bool
    {
        if (! parent::authorize()) {
            return false;
        }

        // Nesting under a cart changes that cart's tree.
        $parent = is_string($this->get('parent_id')) ? Cart::query()->find($this->get('parent_id')) : null;

        return ! $parent instanceof Cart || $this->allows('update', $parent);
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
