<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Sharing\Policies;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\Sharing\Models\CartMemberModel;
use JayI\Polycart\Support\Policies\Policy;

/**
 * Seeing who a cart is shared with needs `view` on the cart; sharing it,
 * changing a role or removing someone needs `share`.
 */
class CartMemberPolicy extends Policy
{
    public function viewAny(Model $user, CartModel $cart): bool
    {
        return $this->allowsOn($user, 'view', $cart);
    }

    public function view(Model $user, CartMemberModel $member): bool
    {
        return $this->allowsOn($user, 'view', $member->cart);
    }

    public function create(Model $user, CartModel $cart): bool
    {
        return $this->allowsOn($user, 'share', $cart);
    }

    public function update(Model $user, CartMemberModel $member): bool
    {
        return $this->allowsOn($user, 'share', $member->cart);
    }

    public function delete(Model $user, CartMemberModel $member): bool
    {
        return $this->allowsOn($user, 'share', $member->cart);
    }
}
