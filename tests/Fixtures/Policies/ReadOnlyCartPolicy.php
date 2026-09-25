<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Policies;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Policies\CartPolicy;

/**
 * Nobody may change a cart, not even its owner.
 */
final class ReadOnlyCartPolicy extends CartPolicy
{
    public function update(Model $user, Cart $cart): bool
    {
        return false;
    }
}
