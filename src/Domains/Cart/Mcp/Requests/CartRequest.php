<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Mcp\Requests;

use Illuminate\Database\Eloquent\Collection;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;
use JayI\Polycart\Mcp\Request;

abstract class CartRequest extends Request
{
    protected function cart(): CartModel
    {
        /** @var string $id */
        $id = $this->get('cart');

        return CartModel::query()->findOrFail($id);
    }

    /**
     * The lines of this cart a call names, so each can be checked against
     * its policy. Ids that are not this cart's lines are left for the
     * action to reject.
     *
     * @return Collection<int, CartLineModel>
     */
    protected function linesNamed(mixed $ids): Collection
    {
        $ids = array_values(array_filter(is_array($ids) ? $ids : [], is_string(...)));

        return $this->cart()->lines()->whereIn('id', $ids)->get();
    }
}
