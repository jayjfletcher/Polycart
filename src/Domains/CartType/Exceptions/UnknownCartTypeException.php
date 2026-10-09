<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartType\Exceptions;

use RefactorCircus\Polycart\Domains\CartType\Support\CartType;
use RefactorCircus\Polycart\Exceptions\PolycartException;

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
