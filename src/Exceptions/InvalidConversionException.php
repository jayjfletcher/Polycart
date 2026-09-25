<?php

declare(strict_types=1);

namespace JayI\Polycart\Exceptions;

final class InvalidConversionException extends PolycartException
{
    public static function notAllowed(string $from, string $to): self
    {
        return new self(sprintf('A [%s] cart cannot be converted into a [%s] cart.', $from, $to));
    }
}
