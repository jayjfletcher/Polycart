<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use Illuminate\Database\Eloquent\Collection;
use JayI\Polycart\Events\Action\MembersListedActionEvent;
use JayI\Polycart\Events\Action\MembersListingActionEvent;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartMember;

final class ListMembersAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    /**
     * @return Collection<int, CartMember>
     */
    public function execute(Cart $cart): Collection
    {
        MembersListingActionEvent::dispatch($cart);

        $result = $this->perform($cart);

        MembersListedActionEvent::dispatch($cart, $result);

        return $result;
    }

    /**
     * @return Collection<int, CartMember>
     */
    private function perform(Cart $cart): Collection
    {
        return $cart->members()->oldest()->get();
    }
}
