<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\Relation;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;
use JayI\Polycart\Domains\Scope\Models\CartPathModel;
use JayI\Polycart\Domains\Sharing\Models\CartMemberModel;
use JayI\Polycart\Facades\Polycart;

it('keeps the class names the models were stored under before they moved', function (string $old, string $model): void {
    expect(Relation::getMorphedModel($old))->toBe($model)
        ->and((new $model)->getMorphClass())->toBe($old);
})->with([
    ['JayI\\Polycart\\Models\\Cart', CartModel::class],
    ['JayI\\Polycart\\Models\\CartLine', CartLineModel::class],
    ['JayI\\Polycart\\Models\\CartMember', CartMemberModel::class],
    ['JayI\\Polycart\\Models\\CartPath', CartPathModel::class],
]);

it('resolves a polymorphic value stored under an old class name', function (): void {
    $cart = Polycart::create('cart');

    $class = Relation::getMorphedModel('JayI\\Polycart\\Models\\Cart');

    expect($class)->not->toBeNull()
        ->and($class::query()->find($cart->getKey())?->is($cart))->toBeTrue();
});

it('still stores a cart type by its key', function (): void {
    $cart = Polycart::create('quote');

    expect(CartModel::query()->toBase()->where('id', $cart->getKey())->value('type'))->toBe('quote');
});
