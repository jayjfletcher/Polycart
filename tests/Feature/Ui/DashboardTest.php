<?php

declare(strict_types=1);

use Atrium\Atrium\Plugins\PluginRegistry;
use Atrium\Atrium\Widgets\WidgetDefinition;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use JayI\Polycart\Atrium\PolycartPlugin;
use JayI\Polycart\Facades\Polycart;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Tests\Fixtures\Types\QuoteStatus;

beforeEach(function (): void {
    ValidateCsrfToken::except(['*']);

    // Atrium denies access outside local until a gate is defined.
    app()->detectEnvironment(fn (): string => 'local');
});

it('registers itself with atrium', function (): void {
    expect(app(PluginRegistry::class)->has('polycart'))->toBeTrue()
        ->and(route('atrium.polycart.carts.index'))->toContain('/atrium/polycart/carts');
});

it('offers widgets without placing any', function (): void {
    $keys = array_map(
        fn (WidgetDefinition $definition): string => $definition->key,
        app(PolycartPlugin::class)->widgets(),
    );

    expect($keys)->toBe(['polycart.carts-by-type', 'polycart.recent-carts']);
});

it('lists carts and filters by type', function (): void {
    Polycart::create('quote', attributes: ['label' => 'Tower lobby']);
    Polycart::create('cart', attributes: ['label' => 'Office']);

    $this->get(route('atrium.polycart.carts.index'))
        ->assertOk()
        ->assertSee('Tower lobby')
        ->assertSee('Office');

    $this->get(route('atrium.polycart.carts.index', ['type' => 'quote']))
        ->assertOk()
        ->assertSee('Tower lobby')
        ->assertDontSee('Office');
});

it('shows a cart with only the moves its type allows', function (): void {
    $quote = Polycart::create('quote', attributes: ['label' => 'Lobby']);
    $quote->add(product(price: 1250), 2);

    $this->get(route('atrium.polycart.carts.show', $quote))
        ->assertOk()
        ->assertSee('25.00')
        ->assertSee('value="submitted"', false)
        ->assertDontSee('value="accepted"', false)
        ->assertSee('value="order"', false);
});

it('moves, converts, and refuses from the dashboard', function (): void {
    $quote = Polycart::create('quote');
    $quote->add(product());

    $this->post(route('atrium.polycart.carts.transition', $quote), ['status' => 'accepted'])
        ->assertRedirect()
        ->assertSessionHasErrors('polycart');

    $this->post(route('atrium.polycart.carts.transition', $quote), ['status' => 'submitted'])
        ->assertRedirect(route('atrium.polycart.carts.show', $quote))
        ->assertSessionHas('status');

    expect($quote->fresh()?->hasStatus(QuoteStatus::Submitted))->toBeTrue();

    $response = $this->post(route('atrium.polycart.carts.convert', $quote), ['to' => 'order', 'copy' => '1']);

    $order = Cart::query()->ofType('order')->firstOrFail();

    $response->assertRedirect(route('atrium.polycart.carts.show', $order));
});

it('edits lines and details, then clears and deletes', function (): void {
    $cart = Polycart::create('cart');
    $line = $cart->add(product(), 2);

    $this->patch(route('atrium.polycart.carts.lines.update', [$cart, $line]), ['quantity' => 7])->assertRedirect();
    expect($line->fresh()?->quantity)->toBe(7);

    $this->patch(route('atrium.polycart.carts.update', $cart), ['label' => 'Renamed', 'meta' => '{"po":"1"}'])->assertRedirect();
    expect($cart->fresh()?->label)->toBe('Renamed')
        ->and($cart->fresh()?->meta)->toBe(['po' => '1']);

    $this->delete(route('atrium.polycart.carts.lines.destroy', [$cart, $line]))->assertRedirect();
    expect($cart->lines()->count())->toBe(0);

    $cart->add(product(sku: 'B'));
    $this->post(route('atrium.polycart.carts.clear', $cart))->assertRedirect();
    expect($cart->lines()->count())->toBe(0);

    $this->delete(route('atrium.polycart.carts.destroy', $cart))->assertRedirect(route('atrium.polycart.carts.index'));
    expect(Cart::query()->find($cart->id))->toBeNull();
});

it('lists the cart types', function (): void {
    $this->get(route('atrium.polycart.types.index'))
        ->assertOk()
        ->assertSee('quote')
        ->assertSee('submitted')
        ->assertSee('6 weeks 3 days');
});

it('renders its widgets', function (): void {
    Polycart::create('quote', attributes: ['label' => 'Widget cart']);

    [$byType, $recent] = app(PolycartPlugin::class)->widgets();

    expect(view('polycart::ui.widgets.carts-by-type', $byType->resolveData())->render())->toContain('quote')
        ->and(view('polycart::ui.widgets.recent-carts', $recent->resolveData())->render())->toContain('Widget cart');
});

it('finds carts from atrium search', function (): void {
    Polycart::create('quote', attributes: ['label' => 'Searchable lobby']);

    $results = app(PolycartPlugin::class)->search()?->results('lobby') ?? [];

    expect($results)->toHaveCount(1);
});

it('manages members and visibility from the dashboard', function (): void {
    $acme = organization();
    $sales = team($acme);
    $ann = person($sales);
    $cart = Polycart::create('cart', $ann, scope: $sales);

    $this->get(route('atrium.polycart.carts.show', $cart))
        ->assertOk()
        ->assertSee('Team #'.$sales->id)
        ->assertSee('Organization #'.$acme->id)
        ->assertSee('owner');

    $this->post(route('atrium.polycart.carts.members.store', $cart), ['member_type' => 'person', 'member_id' => (string) person($sales)->id, 'role' => 'editor'])
        ->assertSessionHas('status');

    $this->post(route('atrium.polycart.carts.members.store', $cart), ['member_type' => 'team', 'member_id' => (string) team(organization('Globex'))->id, 'role' => 'viewer'])
        ->assertSessionHasErrors('polycart');

    $this->post(route('atrium.polycart.carts.members.store', $cart), ['member_type' => 'person', 'member_id' => '999', 'role' => 'viewer'])
        ->assertSessionHasErrors('polycart');

    $this->put(route('atrium.polycart.carts.visibility', $cart), ['visibility' => 'boundary'])->assertSessionHas('status');
    expect($cart->fresh()?->visibility?->value)->toBe('boundary');

    $editor = $cart->members()->where('role', 'editor')->firstOrFail();
    $this->delete(route('atrium.polycart.carts.members.destroy', [$cart, $editor]))->assertSessionHas('status');

    $owner = $cart->members()->firstOrFail();
    $this->delete(route('atrium.polycart.carts.members.destroy', [$cart, $owner]))->assertSessionHasErrors('polycart');
});
