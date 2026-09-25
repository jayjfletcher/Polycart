<?php

declare(strict_types=1);

namespace JayI\Polycart\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Every refusal the package makes: an unknown type, a rejected line, a move
 * or conversion the type does not allow.
 */
abstract class PolycartException extends RuntimeException
{
    /**
     * Answer a JSON caller with 422 and the message, which says what to fix.
     * Anyone else gets Laravel's usual handling.
     */
    public function render(Request $request): JsonResponse|false
    {
        if (! $request->expectsJson()) {
            return false;
        }

        return new JsonResponse(['message' => $this->getMessage()], 422);
    }
}
