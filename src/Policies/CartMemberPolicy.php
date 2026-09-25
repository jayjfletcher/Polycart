<?php

declare(strict_types=1);

namespace JayI\Polycart\Policies;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartMember;

/**
 * Seeing who a cart is shared with needs `view` on the cart; sharing it,
 * changing a role or removing someone needs `share`.
 */
class CartMemberPolicy extends Policy
{
    public function viewAny(Model $user, Cart $cart): bool
    {
        return $this->allowsOnCart($user, 'view', $cart);
    }

    public function view(Model $user, CartMember $member): bool
    {
        return $this->allowsOnCart($user, 'view', $member->cart);
    }

    public function create(Model $user, Cart $cart): bool
    {
        return $this->allowsOnCart($user, 'share', $cart);
    }

    public function update(Model $user, CartMember $member): bool
    {
        return $this->allowsOnCart($user, 'share', $member->cart);
    }

    public function delete(Model $user, CartMember $member): bool
    {
        return $this->allowsOnCart($user, 'share', $member->cart);
    }
}
