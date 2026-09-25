<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use JayI\Polycart\Events\Action\CartsMergedActionEvent;
use JayI\Polycart\Facades\Polycart;
use JayI\Polycart\Models\Cart;

it('moves a guest cart into the customer\'s cart at login', function (): void {
    Event::fake([CartsMergedActionEvent::class]);

    $shared = product(sku: 'SHARED');

    $guest = Polycart::active('cart', 'session-1');
    $guest->add($shared, 2);
    $guest->add(product(sku: 'GUEST'), 1, unitPrice: 300);

    $mine = Polycart::create('cart');
    $mine->add($shared, 1);

    Polycart::merge($guest, $mine);

    expect($mine->lines)->toHaveCount(2)
        ->and($mine->lines->first()?->quantity)->toBe(3)
        ->and($mine->lines->last()?->unit_price)->toBe(300)
        ->and(Cart::query()->find($guest->id))->toBeNull();

    Event::assertDispatched(CartsMergedActionEvent::class, fn (CartsMergedActionEvent $event): bool => $event->from->is($guest) && $event->into->is($mine));
});

it('skips lines whose purchasable has been deleted', function (): void {
    $gone = product(sku: 'GONE');

    $guest = Polycart::create('cart');
    $guest->add($gone);
    $guest->add(null, meta: ['description' => 'Custom']);
    $gone->delete();

    $mine = Polycart::merge($guest, Polycart::create('cart'));

    expect($mine->lines)->toHaveCount(1)
        ->and($mine->lines->first()?->isCustom())->toBeTrue();
});

it('does nothing when merging a cart into itself', function (): void {
    $cart = Polycart::create('cart');
    $cart->add(product());

    Polycart::merge($cart, $cart);

    expect($cart->fresh()?->lines)->toHaveCount(1);
});
