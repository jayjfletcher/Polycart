<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Requests;

use JayI\Polycart\Mcp\Request;
use JayI\Polycart\Models\Cart;

abstract class CartRequest extends Request
{
    protected function cart(): Cart
    {
        /** @var string $id */
        $id = $this->get('cart');

        return Cart::query()->findOrFail($id);
    }
}
