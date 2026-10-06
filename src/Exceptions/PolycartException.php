<?php

declare(strict_types=1);

namespace JayI\Polycart\Exceptions;

use JayI\Foundation\Exceptions\PackageException;

/**
 * Every refusal the package makes: an unknown type, a rejected line, a move
 * or conversion the type does not allow.
 */
abstract class PolycartException extends PackageException
{
    /**
     * The JSON API answers 422 with the message, which says what to fix.
     */
    protected int $status = 422;
}
