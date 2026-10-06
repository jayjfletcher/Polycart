<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Policies;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;
use JayI\Polycart\Support\Policies\Policy;

/**
 * Lines are cart content, so each check defers to the cart: reading a line
 * needs `view` on its cart, changing one needs `update`.
 */
class CartLinePolicy extends Policy
{
    public function viewAny(Model $user, CartModel $cart): bool
    {
        return $this->allowsOn($user, 'view', $cart);
    }

    public function view(Model $user, CartLineModel $line): bool
    {
        return $this->allowsOn($user, 'view', $line->cart);
    }

    public function create(Model $user, CartModel $cart): bool
    {
        return $this->allowsOn($user, 'update', $cart);
    }

    public function update(Model $user, CartLineModel $line): bool
    {
        return $this->allowsOn($user, 'update', $line->cart);
    }

    public function delete(Model $user, CartLineModel $line): bool
    {
        return $this->allowsOn($user, 'update', $line->cart);
    }
}
