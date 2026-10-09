<?php

declare(strict_types=1);

use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Facades\Polycart;

it('still stores a cart type by its key', function (): void {
    $cart = Polycart::create('quote');

    expect(CartModel::query()->toBase()->where('id', $cart->getKey())->value('type'))->toBe('quote');
});
