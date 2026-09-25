<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use JayI\Polycart\Events\Action\CartConvertedActionEvent;
use JayI\Polycart\Exceptions\InvalidConversionException;
use JayI\Polycart\Exceptions\LineRejectedException;
use JayI\Polycart\Facades\Polycart;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Tests\Fixtures\Models\Quote;
use JayI\Polycart\Tests\Fixtures\Models\Service;
use JayI\Polycart\Tests\Fixtures\Types\OrderStatus;
use JayI\Polycart\Tests\Fixtures\Types\QuoteStatus;

it('copies a cart into a quote and leaves the cart alone', function (): void {
    Event::fake([CartConvertedActionEvent::class]);

    $cart = Polycart::active('cart', 'session-1');
    $cart->forceFill(['label' => 'Lobby', 'meta' => ['po_number' => 'PO-7', 'gift_note' => 'hi']])->save();
    $cart->add(product(sku: 'A'), 2);
    $cart->add(product(sku: 'B'), 1, ['finish' => 'gold']);

    $quote = $cart->convertTo('quote');

    expect($quote)->toBeInstanceOf(Quote::class)
        ->and($quote->id)->not->toBe($cart->id)
        ->and($quote->currentStatus())->toBe(QuoteStatus::Draft)
        ->and($quote->session_key)->toBe('session-1')
        ->and($quote->label)->toBe('Lobby')
        ->and($quote->meta)->toBe(['po_number' => 'PO-7'])
        ->and($quote->lines)->toHaveCount(2)
        ->and($quote->subtotal())->toBe($cart->subtotal())
        ->and($quote->lines->first()?->options)->toBe($cart->lines->first()?->options)
        ->and($cart->fresh()?->lines)->toHaveCount(2);

    Event::assertDispatched(
        CartConvertedActionEvent::class,
        fn (CartConvertedActionEvent $event): bool => $event->source->is($cart) && $event->cart->is($quote)
            && $event->from === 'cart' && $event->to === 'quote' && $event->copy,
    );
});

it('retypes a cart in place', function (): void {
    $cart = Polycart::create('cart');
    $cart->add(product(), 3);

    $order = Polycart::convert($cart, 'order', copy: false);

    expect($order->id)->toBe($cart->id)
        ->and($order->type)->toBe('order')
        ->and($order->currentStatus())->toBe(OrderStatus::Pending)
        ->and($order->expires_at)->toBeNull()
        ->and(Cart::query()->count())->toBe(1);
});

it('refuses a conversion the source type does not offer', function (): void {
    Polycart::create('saved')->convertTo('order');
})->throws(InvalidConversionException::class, 'A [saved] cart cannot be converted into a [order] cart.');

it('refuses to carry lines the target cannot hold and writes nothing', function (): void {
    $cart = Polycart::create('cart');
    $cart->add(product());
    $cart->add(Service::query()->create());

    try {
        $cart->convertTo('quote');
    } finally {
        expect(Cart::query()->count())->toBe(1);
    }
})->throws(LineRejectedException::class, 'needs a unit price');
