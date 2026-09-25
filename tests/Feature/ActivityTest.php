<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Testing\Fluent\AssertableJson;
use JayI\Polycart\Actions\DeleteCartAction;
use JayI\Polycart\Actions\UpdateCartAction;
use JayI\Polycart\Enums\Activity;
use JayI\Polycart\Enums\Visibility;
use JayI\Polycart\Facades\Polycart;
use JayI\Polycart\Mcp\PolycartServer;
use JayI\Polycart\Mcp\Tools\ConvertCartTool;
use JayI\Polycart\Mcp\Tools\ListActivityTool;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartActivity;
use JayI\Polycart\Tests\Fixtures\Types\QuoteStatus;

/**
 * @return array<int, string>
 */
function actions(Cart $cart): array
{
    return $cart->activities()->pluck('action')->all();
}

it('records every change the package makes', function (): void {
    $sales = team(organization());
    $ann = person($sales);

    $quote = Polycart::create('quote', $ann, scope: $sales);
    $line = $quote->add(product());
    $quote->add(product(sku: 'B'));
    $quote->updateLine($line, 3);
    $quote->remove($line);
    $quote->clear();
    Polycart::share($quote, person($sales), 'viewer');
    $quote->setVisibility(Visibility::Scope);
    $quote->transitionTo(QuoteStatus::Submitted);
    app(UpdateCartAction::class)->execute($quote, ['label' => 'Renamed']);
    app(DeleteCartAction::class)->execute($quote);

    expect(actions($quote))->toBe([
        'created', 'line_added', 'line_added', 'line_updated', 'line_removed', 'line_removed', 'cleared',
        'shared', 'visibility_changed', 'status_changed', 'updated', 'deleted',
    ]);

    $status = $quote->activities()->where('action', 'status_changed')->sole();
    $updated = $quote->activities()->where('action', 'updated')->sole();

    expect($status->context)->toBe(['from' => 'draft', 'to' => 'submitted'])
        ->and($updated->context)->toBe(['changed' => ['label']])
        ->and($status->source)->toBe('code');
});

it('records the signed-in user as the actor', function (): void {
    $sales = team(organization());
    $ann = person($sales);

    $this->actingAs($ann);
    $cart = Polycart::create('cart', $ann, scope: $sales);

    $created = $cart->activities()->sole();

    expect($created->actor_type)->toBe($ann->getMorphClass())
        ->and($created->actor_id)->toBe((string) $ann->id)
        ->and($created->actor?->is($ann))->toBeTrue();
});

it('keeps every source that touches a cart, once, in order', function (): void {
    $cartId = $this->postJson('/polycart/carts', ['type' => 'cart'])->json('data.id');
    $cart = Cart::query()->findOrFail($cartId);

    Polycart::usingSource('import', fn (): mixed => $cart->add(product()));
    $this->postJson("/polycart/carts/{$cartId}/lines", ['lines' => [['unit_price' => 100]]])->assertCreated();
    $cart->add(product(sku: 'C'));

    expect($cart->fresh()?->sources)->toBe(['api', 'import', 'code'])
        ->and($cart->fresh()?->source)->toBe('api');
});

it('carries the history of an API cart into the order MCP made from it', function (bool $copy): void {
    $cartId = $this->postJson('/polycart/carts', ['type' => 'cart'])->json('data.id');
    $this->postJson("/polycart/carts/{$cartId}/lines", ['lines' => [['unit_price' => 100]]])->assertCreated();

    PolycartServer::tool(ConvertCartTool::class, ['cart' => $cartId, 'to' => 'order', 'copy' => $copy])->assertOk();

    $order = Cart::query()->ofType('order')->sole();

    expect($order->source)->toBe('mcp')
        ->and($order->sources)->toBe(['api', 'mcp'])
        ->and($order->activities()->pluck('source')->unique()->values()->all())->toBe(['api', 'mcp']);

    $copy
        ? expect(actions($order))->toBe(['created', 'line_added', 'created', 'converted', 'converted_from'])
            ->and(Cart::query()->findOrFail($cartId)->sources)->toBe(['api', 'mcp'])
        : expect(actions($order))->toBe(['created', 'line_added', 'converted']);
})->with(['copied' => true, 'in place' => false]);

it('carries a merged cart\'s history into the cart it joins', function (): void {
    $guest = Polycart::usingSource('web', fn (): Cart => Polycart::create('cart', 'session-1'));
    Polycart::usingSource('web', fn (): mixed => $guest->add(product()));

    $mine = Polycart::create('cart');
    Polycart::merge($guest, $mine);

    // The guest's web visit came first, so it is listed first.
    expect($mine->fresh()?->sources)->toBe(['web', 'code'])
        ->and(actions($mine))->toContain('merged')
        ->and(Cart::withTrashed()->findOrFail($guest->id)->activities()->pluck('action')->last())->toBe('merged_into');
});

it('lets the application record its own events', function (): void {
    $cart = Polycart::create('cart');

    $entry = Polycart::usingSource('erp', fn (): CartActivity => $cart->recordActivity('exported', ['reference' => 'SO-1']));

    expect($entry->action)->toBe('exported')
        ->and($entry->source)->toBe('erp')
        ->and($entry->context)->toBe(['reference' => 'SO-1'])
        ->and($cart->fresh()?->sources)->toBe(['code', 'erp'])
        ->and(Activity::from('line_added'))->toBe(Activity::LineAdded);
});

it('finds carts any source has touched', function (): void {
    $touched = Polycart::create('cart');
    Polycart::usingSource('mcp', fn (): mixed => $touched->add(product()));
    Polycart::create('cart');

    expect(Cart::query()->touchedBy('mcp')->pluck('id')->all())->toBe([$touched->id])
        ->and(Cart::query()->touchedBy('code')->count())->toBe(2);

    $this->getJson('/polycart/carts?touched_by=mcp')->assertOk()->assertJsonCount(1, 'data');
});

it('serves the activity log over the API and MCP', function (): void {
    $cart = Polycart::create('cart');
    $cart->add(product());

    $this->getJson("/polycart/carts/{$cart->id}/activity")
        ->assertOk()
        ->assertJsonPath('data.0.action', 'line_added')
        ->assertJsonPath('data.1.action', 'created');

    $this->getJson("/polycart/carts/{$cart->id}/activity?action=created")->assertJsonCount(1, 'data');
    $this->getJson("/polycart/carts/{$cart->id}")->assertJsonPath('data.sources', ['code']);

    PolycartServer::tool(ListActivityTool::class, ['cart' => $cart->id])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json): AssertableJson => $json
            ->has('data', 2)
            ->where('data.0.action', 'line_added')
            ->where('sources', ['code'])
            ->etc());
});

it('guards the activity log like the cart', function (): void {
    config()->set('polycart.authorization', true);

    $sales = team(organization());
    $cart = Polycart::create('cart', person($sales), scope: $sales);

    $this->actingAs(person($sales))->getJson("/polycart/carts/{$cart->id}/activity")->assertForbidden();
});

it('shows the activity and sources in the dashboard', function (): void {
    ValidateCsrfToken::except(['*']);
    app()->detectEnvironment(fn (): string => 'local');

    $cart = Polycart::create('cart');
    Polycart::usingSource('import', fn (): mixed => $cart->add(product()));

    $this->get(route('atrium.polycart.carts.show', $cart))
        ->assertOk()
        ->assertSee('line_added')
        ->assertSee('import');
});
