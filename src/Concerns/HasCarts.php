<?php

declare(strict_types=1);

namespace JayI\Polycart\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Polycart;

/**
 * Give a model — a user, a team, a customer — carts of its own.
 *
 * @mixin Model
 */
trait HasCarts
{
    /**
     * Every cart this model owns, of every type.
     *
     * @return MorphMany<Cart, $this>
     */
    public function carts(): MorphMany
    {
        return $this->morphMany(Cart::class, 'owner');
    }

    /**
     * This model's current cart of a type in a scope, started if it has none.
     *
     * Each scope has its own: `$user->cart(scope: $teamA)` and
     * `$user->cart(scope: $teamB)` are different carts.
     */
    public function cart(?string $type = null, ?Model $scope = null): Cart
    {
        return app(Polycart::class)->active($type ?? config()->string('polycart.default_type', 'cart'), $this, $scope);
    }

    /**
     * Every cart this model can see: its own, ones shared with it or its
     * teams, and ones its scopes can see.
     *
     * @return Builder<Cart>
     */
    public function accessibleCarts(): Builder
    {
        return Cart::query()->accessibleBy($this);
    }
}
