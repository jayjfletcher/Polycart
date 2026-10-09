<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Tests\Fixtures\Models;

use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;

final class Quote extends CartModel
{
    protected static ?string $cartType = 'quote';

    public function reference(): string
    {
        return 'Q-'.$this->id;
    }
}
