<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use JayI\Polycart\Events\Action\CartCreatedActionEvent;
use JayI\Polycart\Facades\Polycart;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Tests\Fixtures\Models\Quote;
use JayI\Polycart\Tests\Fixtures\Types\QuoteStatus;
use JayI\Polycart\Tests\Fixtures\Types\RetailCart;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

function user(): User
{
    return UserFactory::new()->create();
}

it('starts a cart of a type for an owner', function (): void {
    Event::fake([CartCreatedActionEvent::class]);

    $user = user();
    $cart = Polycart::create('cart', $user, ['label' => 'Office refit']);

    expect($cart->type)->toBe('cart')
        ->and($cart->cartType())->toBeInstanceOf(RetailCart::class)
        ->and($cart->owner?->is($user))->toBeTrue()
        ->and($cart->label)->toBe('Office refit')
        ->and($cart->status)->toBeNull();

    Event::assertDispatched(CartCreatedActionEvent::class, fn (CartCreatedActionEvent $event): bool => $event->cart->is($cart) && $event->cart->type === 'cart');
});

it('hydrates each row as the model its type names', function (): void {
    $quote = Polycart::create('quote', user());
    $cart = Polycart::create('cart', user());

    expect($quote)->toBeInstanceOf(Quote::class)
        ->and(Cart::query()->find($quote->id))->toBeInstanceOf(Quote::class)
        ->and(Cart::query()->find($cart->id))->not->toBeInstanceOf(Quote::class)
        ->and(Polycart::find($quote->id))->toBeInstanceOf(Quote::class);
});

it('scopes a pinned subclass to its own type', function (): void {
    Polycart::create('quote', user());
    Polycart::create('cart', user());

    $quote = Quote::query()->create();

    expect(Quote::query()->count())->toBe(2)
        ->and(Cart::query()->count())->toBe(3)
        ->and($quote->type)->toBe('quote')
        ->and($quote->reference())->toStartWith('Q-');
});

it('starts a cart in its type\'s first status', function (): void {
    $quote = Polycart::create('quote', user());

    expect($quote->currentStatus())->toBe(QuoteStatus::Draft)
        ->and($quote->hasStatus(QuoteStatus::Draft))->toBeTrue()
        ->and($quote->hasStatus('submitted'))->toBeFalse();
});

it('reuses an owner\'s active cart', function (): void {
    $user = user();

    $first = Polycart::active('cart', $user);
    $second = Polycart::active('cart', $user);

    expect($second->id)->toBe($first->id)
        ->and(Polycart::active('cart', user())->id)->not->toBe($first->id)
        ->and(Polycart::active('saved', $user)->id)->not->toBe($first->id);
});

it('keeps guest carts by session key', function (): void {
    $guest = Polycart::active('cart', 'session-abc');

    expect($guest->session_key)->toBe('session-abc')
        ->and($guest->owner_type)->toBeNull()
        ->and(Polycart::active('cart', 'session-abc')->id)->toBe($guest->id)
        ->and(Polycart::active('cart', 'session-xyz')->id)->not->toBe($guest->id);
});

it('starts a fresh active cart once the last has moved past its first status', function (): void {
    $user = user();
    $quote = Polycart::active('quote', $user);

    $quote->add(product());
    $quote->transitionTo(QuoteStatus::Submitted);

    expect(Polycart::active('quote', $user)->id)->not->toBe($quote->id);
});

it('expires a cart after its type\'s lifetime and replaces it', function (): void {
    Carbon::setTestNow('2026-01-01 12:00:00');

    $user = user();
    $cart = Polycart::active('cart', $user);

    expect($cart->expires_at?->toDateString())->toBe('2026-02-15')
        ->and($cart->isExpired())->toBeFalse();

    Carbon::setTestNow('2026-02-16 12:00:00');

    expect($cart->isExpired())->toBeTrue()
        ->and(Polycart::active('cart', $user)->id)->not->toBe($cart->id);
});

it('pushes the expiry back when the cart changes', function (): void {
    Carbon::setTestNow('2026-01-01 12:00:00');
    $cart = Polycart::active('cart', user());

    Carbon::setTestNow('2026-01-30 12:00:00');
    $cart->add(product());

    expect($cart->fresh()?->expires_at?->toDateString())->toBe('2026-03-16');
});

it('gives an owner model its own carts', function (): void {
    $user = user();

    $cart = $user->cart();

    expect($cart->type)->toBe('cart')
        ->and($user->cart()->id)->toBe($cart->id)
        ->and($user->cart('quote'))->toBeInstanceOf(Quote::class)
        ->and($user->carts()->count())->toBe(2);
});

it('filters carts by owner, type and status', function (): void {
    $user = user();
    Polycart::create('cart', $user);
    Polycart::create('quote', $user)->add(product())->cart->transitionTo(QuoteStatus::Submitted);
    Polycart::create('quote', user());

    expect(Cart::query()->ownedBy($user)->count())->toBe(2)
        ->and(Cart::query()->ofType('quote')->count())->toBe(2)
        ->and(Cart::query()->ofType('cart', 'quote')->ownedBy($user)->count())->toBe(2)
        ->and(Cart::query()->whereStatus(QuoteStatus::Submitted)->count())->toBe(1);
});

it('prunes carts once they are past their expiry and the grace period', function (): void {
    Carbon::setTestNow('2026-01-01 12:00:00');
    $stale = Polycart::create('cart', user());
    $quote = Polycart::create('quote', user());

    Carbon::setTestNow('2026-03-20 12:00:00');
    $fresh = Polycart::create('cart', user());

    $this->artisan('model:prune', ['--model' => [Cart::class]])->assertSuccessful();

    expect(Cart::withTrashed()->find($stale->id))->toBeNull()
        ->and(Cart::query()->find($quote->id))->not->toBeNull()
        ->and(Cart::query()->find($fresh->id))->not->toBeNull();
});
