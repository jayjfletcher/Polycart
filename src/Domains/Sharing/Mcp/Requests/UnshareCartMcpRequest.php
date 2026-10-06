<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Sharing\Mcp\Requests;

use JayI\Polycart\Domains\Cart\Mcp\Requests\CartRequest;
use JayI\Polycart\Domains\Sharing\Actions\UnshareCartAction;
use JayI\Polycart\Domains\Sharing\Models\CartMemberModel;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class UnshareCartMcpRequest extends CartRequest
{
    protected function authorize(): bool
    {
        return $this->allows('delete', $this->member());
    }

    protected function rules(): array
    {
        return UnshareCartAction::rules() + [
            'cart' => ['required', 'string', 'max:26'],
            'member' => ['required', 'string', 'max:26'],
        ];
    }

    protected function respond(array $validated): ResponseFactory
    {
        $member = $this->member();

        app(UnshareCartAction::class)->execute($this->cart(), $member);

        return Response::structured(['removed' => $member->id]);
    }

    private function member(): CartMemberModel
    {
        /** @var string $id */
        $id = $this->get('member');

        return $this->cart()->members()->findOrFail($id);
    }
}
