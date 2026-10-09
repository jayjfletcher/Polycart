<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Scope\Exceptions;

use RefactorCircus\Polycart\Domains\Scope\Contracts\CartScope;
use RefactorCircus\Polycart\Exceptions\PolycartException;

final class InvalidScopeException extends PolycartException
{
    public static function notAScope(string $class): self
    {
        return new self(sprintf('[%s] must implement %s to hold carts.', $class, CartScope::class));
    }

    public static function outside(string $scope): self
    {
        return new self(sprintf('The owner does not belong to [%s], so cannot start a cart there.', $scope));
    }

    public static function locked(string $cart): self
    {
        return new self(sprintf('Cart [%s] is locked to the tree it was created in.', $cart));
    }

    public static function differentTree(string $cart, string $other): self
    {
        return new self(sprintf('Cart [%s] and cart [%s] are not in the same scope.', $cart, $other));
    }
}
