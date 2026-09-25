<?php

declare(strict_types=1);

namespace JayI\Polycart\Exceptions;

final class InvalidTransitionException extends PolycartException
{
    public static function unknownStatus(string $type, string $status): self
    {
        return new self(sprintf('[%s] is not a status of a [%s] cart.', $status, $type));
    }

    public static function notAllowed(string $type, ?string $from, string $to): self
    {
        return new self(sprintf(
            'A [%s] cart cannot move from [%s] to [%s].',
            $type,
            $from ?? 'none',
            $to,
        ));
    }
}
