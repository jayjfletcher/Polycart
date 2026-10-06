<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Schema;
use JayI\Keen\Domains\Audit\Models\AuditEntryModel;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Facades\Polycart;
use JayI\Polycart\Mcp\PolycartServer;
use JayI\Polycart\Mcp\Tools\ListPolycartHistoryTool;

beforeEach(function (): void {
    ValidateCsrfToken::except(['*']);

    // Atrium denies access outside local until a gate is defined.
    app()->detectEnvironment(fn (): string => 'local');
});

/**
 * @return Collection<int, AuditEntryModel>
 */
function polycartEntries(string $action): Collection
{
    return AuditEntryModel::query()->where('source', 'polycart')->where('action', $action)->get();
}

it('records adding a line as an entry about the cart', function (): void {
    $cart = Polycart::create('cart', attributes: ['label' => 'Lobby']);
    $line = $cart->add(product(), 2);

    $entry = polycartEntries('line.added')->sole();

    expect($entry->subject_type)->toBe($cart->getMorphClass())
        ->and($entry->subject_id)->toBe($cart->id)
        ->and($entry->subject_label)->toBe('Lobby')
        ->and($entry->context)->toMatchArray(['line' => $line->id, 'quantity' => 2, 'merged' => false]);
});

it('records sharing as an entry about the cart', function (): void {
    $sales = team(organization());
    $cart = Polycart::create('cart', person($sales), scope: $sales);
    $bob = person($sales);

    Polycart::share($cart, $bob, 'viewer');

    $entry = polycartEntries('cart.shared')->sole();

    expect($entry->subject_id)->toBe($cart->id)
        ->and($entry->context)->toMatchArray(['member_id' => (string) $bob->id, 'role' => 'viewer']);
});

it('records a merge on the cart merged into, naming where its lines came from', function (): void {
    $guest = Polycart::create('cart', 'guest-1');
    $guest->add(product());
    $mine = Polycart::create('cart');

    Polycart::merge($guest, $mine);

    $entry = polycartEntries('carts.merged')->sole();

    expect($entry->subject_id)->toBe($mine->id)
        ->and($entry->context)->toMatchArray(['merged_from' => $guest->id]);
});

it('records a copying conversion on the copy, naming the original', function (): void {
    $cart = Polycart::create('cart');
    $cart->add(product());

    $quote = $cart->convertTo('quote');

    $entry = polycartEntries('cart.converted')->sole();

    expect($entry->subject_id)->toBe($quote->id)
        ->and($entry->context)->toMatchArray(['converted_from' => $cart->id, 'from' => 'cart', 'to' => 'quote']);
});

it('lists a cart\'s history on its dashboard screen', function (): void {
    $guest = Polycart::create('cart', 'guest-1');
    $guest->add(product(sku: 'A'));
    $mine = Polycart::create('cart');
    $mine->add(product(sku: 'B'));
    Polycart::merge($guest, $mine);

    $this->get(route('atrium.polycart.carts.show', $mine))
        ->assertOk()
        ->assertSee('data-testid="audit-trail"', false)
        ->assertSee('line.added')
        ->assertSee('carts.merged');
});

it('lists Polycart\'s history on the carts screen', function (): void {
    Polycart::create('cart')->add(product());

    $this->get(route('atrium.polycart.carts.index'))
        ->assertOk()
        ->assertSee('data-testid="audit-trail"', false)
        ->assertSee('line.added');
});

it('serves a cart\'s history from the history endpoint and tool', function (): void {
    $cart = Polycart::create('cart');
    $cart->add(product());

    $query = ['subject_type' => $cart->getMorphClass(), 'subject_id' => $cart->id];

    $this->getJson('/polycart/history?'.http_build_query($query))
        ->assertOk()
        ->assertJsonFragment(['action' => 'line.added']);

    PolycartServer::tool(ListPolycartHistoryTool::class, $query)
        ->assertOk()
        ->assertSee('line.added');
});

it('records the application\'s own events through the deprecated recordActivity', function (): void {
    $cart = Polycart::create('cart');

    $cart->recordActivity('exported', ['reference' => 'SO-1']);

    $entry = AuditEntryModel::query()->where('action', 'cart.exported')->sole();

    expect($entry->source)->toBe('app')
        ->and($entry->subject_id)->toBe($cart->id)
        ->and($entry->context)->toMatchArray(['reference' => 'SO-1']);
});

it('keeps every cart change in the audit log rather than a table of its own', function (): void {
    expect(Schema::hasTable('polycart_cart_activities'))->toBeFalse()
        ->and(Schema::hasColumn('polycart_carts', 'sources'))->toBeFalse()
        ->and(method_exists(CartModel::class, 'activities'))->toBeFalse();
});
