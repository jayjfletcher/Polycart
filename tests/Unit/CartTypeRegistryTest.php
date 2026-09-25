<?php

declare(strict_types=1);

use JayI\Polycart\Exceptions\CartTypeCollisionException;
use JayI\Polycart\Exceptions\UnknownCartTypeException;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Tests\Fixtures\Models\Product;
use JayI\Polycart\Tests\Fixtures\Models\Quote;
use JayI\Polycart\Tests\Fixtures\Types\OrderCart;
use JayI\Polycart\Tests\Fixtures\Types\QuoteCart;
use JayI\Polycart\Tests\Fixtures\Types\SavedCart;
use JayI\Polycart\Types\CartTypeRegistry;

it('lets a package register a type at runtime', function (): void {
    $registry = app(CartTypeRegistry::class);

    $registry->register('wishlist', SavedCart::class);

    expect($registry->has('wishlist'))->toBeTrue()
        ->and($registry->get('wishlist'))->toBeInstanceOf(SavedCart::class)
        ->and($registry->get('wishlist')->key())->toBe('wishlist');
});

it('derives a key from the class when none is given', function (): void {
    $registry = app(CartTypeRegistry::class);

    $registry->registerMany([OrderCart::class]);

    expect($registry->class('order_cart'))->toBe(OrderCart::class);
});

it('gives the application config precedence over a package registration', function (): void {
    $registry = app(CartTypeRegistry::class);

    $registry->register('quote', OrderCart::class);

    expect($registry->get('quote'))->toBeInstanceOf(QuoteCart::class);
});

it('refuses two classes for one key', function (): void {
    $registry = app(CartTypeRegistry::class);

    $registry->register('wishlist', SavedCart::class);
    $registry->register('wishlist', SavedCart::class);
    $registry->register('wishlist', OrderCart::class);
})->throws(CartTypeCollisionException::class);

it('refuses a class that is not a cart type', function (): void {
    /** @phpstan-ignore argument.type */
    app(CartTypeRegistry::class)->register('product', Product::class);
})->throws(UnknownCartTypeException::class, 'must extend');

it('throws for an unknown key', function (): void {
    app(CartTypeRegistry::class)->get('nope');
})->throws(UnknownCartTypeException::class, '[nope]');

it('names the model a type hydrates as', function (): void {
    $registry = app(CartTypeRegistry::class);

    expect($registry->model('quote'))->toBe(Quote::class)
        ->and($registry->model('order'))->toBe(Cart::class)
        ->and($registry->model('nope'))->toBeNull();
});
