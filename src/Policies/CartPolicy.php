<?php

declare(strict_types=1);

namespace JayI\Polycart\Policies;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Models\Cart;

/**
 * Answers `$user->can(...)` for carts.
 *
 * The cart's owner may do anything. Everyone else gets what their role on the
 * cart grants, as the cart's type defines it. Any ability a type adds —
 * `checkout`, `approve` — works without a method here. Point
 * `polycart.policies` at your own class to replace this one.
 */
class CartPolicy extends Policy
{
    /**
     * Listings are already limited to the carts the user can access.
     */
    public function viewAny(Model $user): bool
    {
        return true;
    }

    public function create(Model $user): bool
    {
        return true;
    }

    public function view(Model $user, Cart $cart): bool
    {
        return $this->ownsOrAllows($user, $cart, 'view');
    }

    public function update(Model $user, Cart $cart): bool
    {
        return $this->ownsOrAllows($user, $cart, 'update');
    }

    public function delete(Model $user, Cart $cart): bool
    {
        return $this->ownsOrAllows($user, $cart, 'delete');
    }

    public function share(Model $user, Cart $cart): bool
    {
        return $this->ownsOrAllows($user, $cart, 'share');
    }

    /**
     * Moving to a status needs whatever ability the type names for that move.
     */
    public function transition(Model $user, Cart $cart, string $status): bool
    {
        $statuses = $cart->cartType()->statuses();
        $to = $statuses === null ? null : $statuses::tryFrom($status);

        $ability = $to === null ? 'transition' : $cart->cartType()->transitionAbility($cart->currentStatus(), $to);

        return $this->ownsOrAllows($user, $cart, $ability);
    }

    /**
     * Converting needs whatever ability the type names for that target.
     */
    public function convert(Model $user, Cart $cart, string $to): bool
    {
        return $this->ownsOrAllows($user, $cart, $cart->cartType()->conversionAbility($to));
    }

    /**
     * Any other ability is looked up in the type's roles.
     *
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $ability, array $arguments): bool
    {
        [$user, $cart] = $arguments + [null, null];

        return $user instanceof Model && $cart instanceof Cart && $this->ownsOrAllows($user, $cart, $ability);
    }

    protected function ownsOrAllows(Model $user, Cart $cart, string $ability): bool
    {
        return $this->owns($user, $cart) || $cart->allows($user, $ability);
    }
}
