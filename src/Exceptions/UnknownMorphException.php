<?php

declare(strict_types=1);

namespace JayI\Polycart\Exceptions;

final class UnknownMorphException extends PolycartException
{
    public static function notAllowed(string $group, string $type): self
    {
        return new self(sprintf('[%s] is not listed in polycart.%s.', $type, $group));
    }
}
