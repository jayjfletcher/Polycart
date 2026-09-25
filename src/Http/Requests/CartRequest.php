<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Requests;

use JayI\Polycart\Http\Request;
use JayI\Polycart\Models\Cart;

abstract class CartRequest extends Request
{
    protected function cart(): Cart
    {
        $cart = $this->route('cart');

        if (! $cart instanceof Cart) {
            abort(404);
        }

        return $cart;
    }
}
