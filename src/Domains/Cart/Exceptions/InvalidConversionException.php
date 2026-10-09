<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Exceptions;

use RefactorCircus\Polycart\Exceptions\PolycartException;

final class InvalidConversionException extends PolycartException
{
    public static function notAllowed(string $from, string $to): self
    {
        return new self(sprintf('A [%s] cart cannot be converted into a [%s] cart.', $from, $to));
    }
}
