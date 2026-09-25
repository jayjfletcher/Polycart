<?php

declare(strict_types=1);

namespace JayI\Polycart\Contracts;

/**
 * Someone who works with carts inside the application's tree, usually a user.
 *
 * Polycart asks for the scopes a participant belongs to directly — their
 * teams — and walks up from each one itself. It asks every time access is
 * checked, so joining or leaving a team takes effect immediately.
 */
interface CartParticipant
{
    /**
     * The scopes this participant belongs to directly.
     *
     * @return iterable<int, CartScope>
     */
    public function cartScopes(): iterable;
}
