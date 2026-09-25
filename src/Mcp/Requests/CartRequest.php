<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use Illuminate\Database\Eloquent\Collection;
use JayI\Polycart\Mcp\Request;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartLine;

abstract class CartRequest extends Request
{
    protected function cart(): Cart
    {
        /** @var string $id */
        $id = $this->get('cart');

        return Cart::query()->findOrFail($id);
    }

    /**
     * The lines of this cart a call names, so each can be checked against
     * its policy. Ids that are not this cart's lines are left for the
     * action to reject.
     *
     * @return Collection<int, CartLine>
     */
    protected function linesNamed(mixed $ids): Collection
    {
        $ids = array_values(array_filter(is_array($ids) ? $ids : [], is_string(...)));

        return $this->cart()->lines()->whereIn('id', $ids)->get();
    }
}
