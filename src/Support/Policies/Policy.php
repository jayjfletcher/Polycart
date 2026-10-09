<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Support\Policies;

use Illuminate\Database\Eloquent\Model;
use RefactorCircus\Keystone\Policies\Policy as KeystonePolicy;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;

/**
 * Shared checks for the bundled policies.
 *
 * Each policy is registered from `polycart.policies`, so an application swaps
 * one by pointing its model at another class there. A model that lives inside
 * a cart asks about its cart with `allowsOn()`, so it follows whichever cart
 * policy is registered.
 */
abstract class Policy extends KeystonePolicy
{
    /**
     * Whether the user is the cart's owner.
     */
    protected function owns(Model $user, CartModel $cart): bool
    {
        return $cart->owner_type !== null
            && $cart->owner_type === $user->getMorphClass()
            && $cart->owner_id === (string) $user->getKey();
    }
}
