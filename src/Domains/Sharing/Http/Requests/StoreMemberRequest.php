<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Sharing\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Domains\Cart\Http\Requests\CartRequest;
use JayI\Polycart\Domains\Sharing\Actions\ShareCartAction;
use JayI\Polycart\Domains\Sharing\Models\CartMemberModel;
use JayI\Polycart\Domains\Sharing\Resources\CartMemberResource;
use JayI\Polycart\Support\Morphs;

final class StoreMemberRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('create', CartMemberModel::class, [$this->cart()]);
    }

    public function rules(): array
    {
        return ShareCartAction::rules();
    }

    public function persist(): JsonResponse
    {
        $data = $this->validated();

        /** @var string $role */
        $role = $data['role'];

        $member = app(ShareCartAction::class)->execute($this->cart(), app(Morphs::class)->memberFrom($data), $role);

        return (new CartMemberResource($member))->response();
    }
}
