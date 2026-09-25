<?php

declare(strict_types=1);

namespace JayI\Polycart\Exceptions;

final class CartTypeCollisionException extends PolycartException
{
    public static function key(string $key, string $existing, string $incoming): self
    {
        return new self(sprintf(
            'The cart type key [%s] is already registered to [%s] and cannot be registered to [%s]. Set it in polycart.types to choose one.',
            $key,
            $existing,
            $incoming,
        ));
    }
}
