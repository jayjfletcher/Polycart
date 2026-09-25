<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Models;

use JayI\Polycart\Models\Cart;

final class Quote extends Cart
{
    protected static ?string $cartType = 'quote';

    public function reference(): string
    {
        return 'Q-'.$this->id;
    }
}
