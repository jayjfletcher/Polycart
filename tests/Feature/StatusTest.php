<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use JayI\Polycart\Events\Action\CartTransitionedActionEvent;
use JayI\Polycart\Exceptions\InvalidTransitionException;
use JayI\Polycart\Facades\Polycart;
use JayI\Polycart\Tests\Fixtures\Types\OrderStatus;
use JayI\Polycart\Tests\Fixtures\Types\QuoteStatus;

it('moves a cart through its own lifecycle', function (): void {
    Event::fake([CartTransitionedActionEvent::class]);

    $quote = Polycart::create('quote');

    $quote->transitionTo(QuoteStatus::Submitted)->transitionTo('accepted');

    expect($quote->fresh()?->currentStatus())->toBe(QuoteStatus::Accepted);

    Event::assertDispatched(
        CartTransitionedActionEvent::class,
        fn (CartTransitionedActionEvent $event): bool => $event->from === 'submitted' && $event->to === 'accepted' && $event->cart->is($quote),
    );
});

it('refuses a move the type does not allow', function (): void {
    Polycart::create('quote')->transitionTo(QuoteStatus::Accepted);
})->throws(InvalidTransitionException::class, 'cannot move from [draft] to [accepted]');

it('treats a status missing from the map as terminal', function (): void {
    $quote = Polycart::create('quote')->transitionTo(QuoteStatus::Submitted)->transitionTo(QuoteStatus::Declined);

    $quote->transitionTo(QuoteStatus::Submitted);
})->throws(InvalidTransitionException::class);

it('refuses another type\'s status', function (): void {
    Polycart::create('quote')->transitionTo(OrderStatus::Shipped);
})->throws(InvalidTransitionException::class, '[shipped] is not a status of a [quote] cart');

it('refuses an unknown status string', function (): void {
    Polycart::create('quote')->transitionTo('lost');
})->throws(InvalidTransitionException::class, 'not a status');

it('refuses any status on a type without a lifecycle', function (): void {
    Polycart::create('cart')->transitionTo('submitted');
})->throws(InvalidTransitionException::class, 'not a status of a [cart] cart');
