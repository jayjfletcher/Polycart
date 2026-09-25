<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Policies;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Models\CartLine;
use JayI\Polycart\Policies\CartLinePolicy;

/**
 * Lines may be changed but never removed.
 */
final class KeepLinesPolicy extends CartLinePolicy
{
    public function delete(Model $user, CartLine $line): bool
    {
        return false;
    }
}
