<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;
use JayI\Polycart\Enums\CartSource;
use JayI\Polycart\Facades\Polycart;
use JayI\Polycart\Http\Middleware\CartSource as CartSourceMiddleware;
use JayI\Polycart\Mcp\PolycartServer;
use JayI\Polycart\Mcp\Tools\ConvertCartTool;
use JayI\Polycart\Mcp\Tools\CreateCartTool;
use JayI\Polycart\Models\Cart;

it('records code as the source of carts made directly', function (): void {
    expect(Polycart::create('cart')->source)->toBe('code');
});

it('uses the configured default source', function (): void {
    config()->set('polycart.default_source', 'web');

    expect(Polycart::create('cart')->source)->toBe('web');
});

it('stamps carts made inside a callback and restores the source after', function (): void {
    $imported = Polycart::usingSource('import', fn (): Cart => Polycart::create('cart'));
    $enum = Polycart::usingSource(CartSource::Atrium, fn (): Cart => Polycart::create('cart'));

    expect($imported->source)->toBe('import')
        ->and($enum->source)->toBe('atrium')
        ->and(Polycart::create('cart')->source)->toBe('code');
});

it('restores the source when the callback throws', function (): void {
    try {
        Polycart::usingSource('import', fn () => throw new RuntimeException('boom'));
    } catch (RuntimeException) {
        //
    }

    expect(Polycart::create('cart')->source)->toBe('code');
});

it('records carts made through the JSON API', function (): void {
    $this->postJson('/polycart/carts', ['type' => 'cart'])
        ->assertCreated()
        ->assertJsonPath('data.source', 'api');

    expect(Polycart::create('cart')->source)->toBe('code');
});

it('records carts made through MCP', function (): void {
    PolycartServer::tool(CreateCartTool::class, ['type' => 'cart'])->assertOk();

    expect(Cart::query()->sole()->source)->toBe('mcp')
        ->and(Polycart::create('cart')->source)->toBe('code');
});

it('records copies made from the dashboard as the dashboard\'s', function (): void {
    ValidateCsrfToken::except(['*']);
    app()->detectEnvironment(fn (): string => 'local');

    $cart = Polycart::create('cart');
    $cart->add(product());

    $this->post(route('atrium.polycart.carts.convert', $cart), ['to' => 'quote', 'copy' => '1'])->assertRedirect();

    expect(Cart::query()->ofType('quote')->sole()->source)->toBe('atrium')
        ->and($cart->fresh()?->source)->toBe('code');
});

it('lets an application tag its own routes', function (): void {
    Route::post('/checkout-web', fn (): string => Polycart::create('cart')->source ?? '')
        ->middleware(CartSourceMiddleware::class.':web');

    $this->post('/checkout-web')->assertSee('web');
});

it('filters carts by source', function (): void {
    Polycart::create('cart');
    Polycart::usingSource('import', fn (): Cart => Polycart::create('cart'));

    expect(Cart::query()->fromSource('import')->count())->toBe(1)
        ->and(Cart::query()->fromSource(CartSource::Code, 'import')->count())->toBe(2);

    $this->getJson('/polycart/carts?source=import')->assertOk()->assertJsonCount(1, 'data');
});

it('records the source of the conversion on the converted cart', function (bool $copy): void {
    $cartId = $this->postJson('/polycart/carts', ['type' => 'cart'])->assertCreated()->json('data.id');
    Cart::query()->findOrFail($cartId)->add(product());

    PolycartServer::tool(ConvertCartTool::class, ['cart' => $cartId, 'to' => 'order', 'copy' => $copy])->assertOk();

    $order = Cart::query()->ofType('order')->sole();

    expect($order->source)->toBe('mcp')
        ->and($order->id === $cartId)->toBe(! $copy);

    if ($copy) {
        expect(Cart::query()->findOrFail($cartId)->source)->toBe('api');
    }
})->with(['copied' => true, 'in place' => false]);
