<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Policies;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;
use JayI\Polycart\Domains\CartLine\Policies\CartLinePolicy;

/**
 * Lines may be changed but never removed.
 */
final class KeepLinesPolicy extends CartLinePolicy
{
    public function delete(Model $user, CartLineModel $line): bool
    {
        return false;
    }
}
