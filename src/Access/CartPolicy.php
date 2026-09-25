<?php

declare(strict_types=1);

namespace JayI\Polycart\Access;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Models\Cart;

/**
 * Answers `$user->can(...)` for carts from the role the cart's type gives
 * them.
 *
 * Any ability a type adds — `checkout`, `approve` — works without a method
 * here: `$user->can('checkout', $cart)` asks whether the user's role on that
 * cart grants it. Register your own policy for Cart to replace this one.
 */
class CartPolicy
{
    public function view(Model $user, Cart $cart): bool
    {
        return $cart->allows($user, 'view');
    }

    public function update(Model $user, Cart $cart): bool
    {
        return $cart->allows($user, 'update');
    }

    public function delete(Model $user, Cart $cart): bool
    {
        return $cart->allows($user, 'delete');
    }

    public function share(Model $user, Cart $cart): bool
    {
        return $cart->allows($user, 'share');
    }

    /**
     * Moving to a status needs whatever ability the type names for that move.
     */
    public function transition(Model $user, Cart $cart, string $status): bool
    {
        $statuses = $cart->cartType()->statuses();
        $to = $statuses === null ? null : $statuses::tryFrom($status);

        $ability = $to === null ? 'transition' : $cart->cartType()->transitionAbility($cart->currentStatus(), $to);

        return $cart->allows($user, $ability);
    }

    /**
     * Converting needs whatever ability the type names for that target.
     */
    public function convert(Model $user, Cart $cart, string $to): bool
    {
        return $cart->allows($user, $cart->cartType()->conversionAbility($to));
    }

    /**
     * Any other ability is looked up in the type's roles.
     *
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $ability, array $arguments): bool
    {
        [$user, $cart] = $arguments + [null, null];

        return $user instanceof Model && $cart instanceof Cart && $cart->allows($user, $ability);
    }
}
