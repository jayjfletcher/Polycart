<?php

declare(strict_types=1);

namespace JayI\Polycart\Policies;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartLine;

/**
 * Lines are cart content, so each check defers to the cart: reading a line
 * needs `view` on its cart, changing one needs `update`.
 */
class CartLinePolicy extends Policy
{
    public function viewAny(Model $user, Cart $cart): bool
    {
        return $this->allowsOnCart($user, 'view', $cart);
    }

    public function view(Model $user, CartLine $line): bool
    {
        return $this->allowsOnCart($user, 'view', $line->cart);
    }

    public function create(Model $user, Cart $cart): bool
    {
        return $this->allowsOnCart($user, 'update', $cart);
    }

    public function update(Model $user, CartLine $line): bool
    {
        return $this->allowsOnCart($user, 'update', $line->cart);
    }

    public function delete(Model $user, CartLine $line): bool
    {
        return $this->allowsOnCart($user, 'update', $line->cart);
    }
}
