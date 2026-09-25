<?php

declare(strict_types=1);

namespace JayI\Polycart\Policies;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartActivity;

/**
 * The activity log is read with the cart and never edited, so there is no
 * create, update or delete ability: the Gate denies them.
 */
class CartActivityPolicy extends Policy
{
    public function viewAny(Model $user, Cart $cart): bool
    {
        return $this->allowsOnCart($user, 'view', $cart);
    }

    public function view(Model $user, CartActivity $activity): bool
    {
        return $this->allowsOnCart($user, 'view', $activity->cart);
    }
}
