<?php

declare(strict_types=1);

namespace JayI\Polycart\Enums;

/**
 * Who can see a cart without being shared in.
 *
 * Members always see a cart. Visibility only widens that, and only inside
 * the cart's own tree.
 */
enum Visibility: string
{
    /** Only members: the creator and whoever it is shared with. */
    case Private = 'private';

    /** Everyone in the cart's own scope, such as its team. */
    case Scope = 'scope';

    /** Everyone inside the cart's boundary, such as its organization. */
    case Boundary = 'boundary';
}
