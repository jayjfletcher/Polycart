<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Actions\ShareCartAction;
use JayI\Polycart\Http\Resources\CartMemberResource;
use JayI\Polycart\Models\CartMember;
use JayI\Polycart\Support\Morphs;

final class StoreMemberRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('create', CartMember::class, [$this->cart()]);
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
