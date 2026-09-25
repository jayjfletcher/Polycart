<?php

declare(strict_types=1);

namespace JayI\Polycart\Exceptions;

final class InvalidParentException extends PolycartException
{
    public static function notAllowed(string $type, string $parent): self
    {
        return new self(sprintf('A [%s] cart cannot be nested under a [%s] cart.', $type, $parent));
    }

    public static function required(string $type): self
    {
        return new self(sprintf('A [%s] cart must be nested under a parent cart.', $type));
    }
}
