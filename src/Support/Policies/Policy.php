<?php

declare(strict_types=1);

namespace JayI\Polycart\Support\Policies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use JayI\Polycart\Domains\Cart\Models\CartModel;

/**
 * Shared checks for the bundled policies.
 *
 * Each policy is registered from `polycart.policies`, so an application swaps
 * one by pointing its model at another class there.
 */
abstract class Policy
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

    /**
     * Ask the Gate about the parent cart, so a model that lives inside a cart
     * follows whichever cart policy is registered.
     *
     * @param  array<int, mixed>  $arguments
     */
    protected function allowsOnCart(Model $user, string $ability, CartModel $cart, array $arguments = []): bool
    {
        return Gate::forUser($user)->allows($ability, [$cart, ...$arguments]);
    }
}
