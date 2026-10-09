<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Tests\Fixtures\Policies;

use Illuminate\Database\Eloquent\Model;
use RefactorCircus\Polycart\Domains\CartLine\Models\CartLineModel;
use RefactorCircus\Polycart\Domains\CartLine\Policies\CartLinePolicy;

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
