<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Requests;

use Illuminate\Http\Response;
use JayI\Polycart\Actions\UnshareCartAction;
use JayI\Polycart\Models\CartMember;

final class DestroyMemberRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('share', $this->cart());
    }

    public function rules(): array
    {
        return UnshareCartAction::rules();
    }

    public function persist(): Response
    {
        $member = $this->route('member');

        if (! $member instanceof CartMember) {
            abort(404);
        }

        app(UnshareCartAction::class)->execute($this->cart(), $member);

        return response()->noContent();
    }
}
