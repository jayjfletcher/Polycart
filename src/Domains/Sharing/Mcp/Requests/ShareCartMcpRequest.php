<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Sharing\Mcp\Requests;

use JayI\Polycart\Domains\Cart\Mcp\Requests\CartRequest;
use JayI\Polycart\Domains\Sharing\Actions\ShareCartAction;
use JayI\Polycart\Domains\Sharing\Models\CartMemberModel;
use JayI\Polycart\Domains\Sharing\Resources\CartMemberResource;
use JayI\Polycart\Support\Morphs;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class ShareCartMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('create', CartMemberModel::class, [$this->cart()]);
    }

    protected function rules(): array
    {
        return ShareCartAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
        ];
    }

    protected function respond(array $validated): ResponseFactory
    {
        /** @var string $role */
        $role = $validated['role'];

        $member = app(ShareCartAction::class)->execute($this->cart(), app(Morphs::class)->memberFrom($validated), $role);

        return Response::structured((new CartMemberResource($member))->resolve());
    }
}
