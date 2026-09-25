<?php

declare(strict_types=1);

namespace JayI\Polycart\Contracts;

/**
 * A level of the application's tree a cart can live in: a team, an
 * organization, a department.
 *
 * Implement it on each Eloquent model that is a level. A cart records the
 * whole chain from its own scope up to the top when it is created, and that
 * chain never changes. The top of the chain is the cart's boundary: it can
 * only be shared inside it.
 */
interface CartScope
{
    /**
     * The level above this one, or null at the top of the tree.
     */
    public function parentCartScope(): ?CartScope;
}
