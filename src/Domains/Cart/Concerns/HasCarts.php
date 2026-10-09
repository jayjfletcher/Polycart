<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Polycart;

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
     * @return MorphMany<CartModel, $this>
     */
    public function carts(): MorphMany
    {
        return $this->morphMany(CartModel::class, 'owner');
    }

    /**
     * This model's current cart of a type in a scope, started if it has none.
     *
     * Each scope has its own: `$user->cart(scope: $teamA)` and
     * `$user->cart(scope: $teamB)` are different carts.
     */
    public function cart(?string $type = null, ?Model $scope = null): CartModel
    {
        return app(Polycart::class)->active($type ?? config()->string('polycart.default_type', 'cart'), $this, $scope);
    }

    /**
     * Every cart this model can see: its own, ones shared with it or its
     * teams, and ones its scopes can see.
     *
     * @return Builder<CartModel>
     */
    public function accessibleCarts(): Builder
    {
        return CartModel::query()->accessibleBy($this);
    }
}
