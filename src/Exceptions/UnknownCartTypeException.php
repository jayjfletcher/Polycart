<?php

declare(strict_types=1);

namespace JayI\Polycart\Exceptions;

use JayI\Polycart\Types\CartType;

final class UnknownCartTypeException extends PolycartException
{
    public static function key(string $key): self
    {
        return new self(sprintf('No cart type is registered under [%s].', $key));
    }

    public static function notACartType(string $class): self
    {
        return new self(sprintf('The cart type [%s] must extend %s.', $class, CartType::class));
    }
}
