<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Actions\UnshareCartAction;
use JayI\Polycart\Models\CartMember;
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

    protected function handle(array $validated): ResponseFactory
    {
        $member = $this->member();

        app(UnshareCartAction::class)->execute($this->cart(), $member);

        return Response::structured(['removed' => $member->id]);
    }

    private function member(): CartMember
    {
        /** @var string $id */
        $id = $this->get('member');

        return $this->cart()->members()->findOrFail($id);
    }
}
