<?php

declare(strict_types=1);

namespace JayI\Polycart\Exceptions;

final class SharingException extends PolycartException
{
    public static function notShareable(string $type): self
    {
        return new self(sprintf('A [%s] cart cannot be shared.', $type));
    }

    public static function unscoped(string $cart): self
    {
        return new self(sprintf('Cart [%s] is not in a scope, so there is no boundary to share it within.', $cart));
    }

    public static function outsideBoundary(string $member, string $boundary): self
    {
        return new self(sprintf('[%s] is outside [%s], so the cart cannot be shared with it.', $member, $boundary));
    }

    public static function unknownRole(string $type, string $role): self
    {
        return new self(sprintf('[%s] is not a role of a [%s] cart.', $role, $type));
    }

    public static function lastOwner(string $cart): self
    {
        return new self(sprintf('Cart [%s] must keep at least one member with its top role.', $cart));
    }
}
