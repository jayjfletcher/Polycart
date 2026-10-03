<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Sharing\Http\Requests;

use Illuminate\Http\Response;
use JayI\Polycart\Domains\Cart\Http\Requests\CartRequest;
use JayI\Polycart\Domains\Sharing\Actions\UnshareCartAction;
use JayI\Polycart\Domains\Sharing\Models\CartMemberModel;

final class DestroyMemberRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('delete', $this->member());
    }

    public function rules(): array
    {
        return UnshareCartAction::rules();
    }

    public function persist(): Response
    {
        app(UnshareCartAction::class)->execute($this->cart(), $this->member());

        return response()->noContent();
    }

    private function member(): CartMemberModel
    {
        $member = $this->route('member');

        if (! $member instanceof CartMemberModel) {
            abort(404);
        }

        return $member;
    }
}
