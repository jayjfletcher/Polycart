<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Activity\Policies;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Domains\Activity\Models\CartActivityModel;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Support\Policies\Policy;

/**
 * The activity log is read with the cart and never edited, so there is no
 * create, update or delete ability: the Gate denies them.
 */
class CartActivityPolicy extends Policy
{
    public function viewAny(Model $user, CartModel $cart): bool
    {
        return $this->allowsOnCart($user, 'view', $cart);
    }

    public function view(Model $user, CartActivityModel $activity): bool
    {
        return $this->allowsOnCart($user, 'view', $activity->cart);
    }
}
