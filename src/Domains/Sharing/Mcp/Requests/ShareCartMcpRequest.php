<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Sharing\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Polycart\Domains\Cart\Mcp\Requests\CartRequest;
use RefactorCircus\Polycart\Domains\Sharing\Actions\ShareCartAction;
use RefactorCircus\Polycart\Domains\Sharing\Models\CartMemberModel;
use RefactorCircus\Polycart\Domains\Sharing\Resources\CartMemberResource;
use RefactorCircus\Polycart\Support\Morphs;

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
