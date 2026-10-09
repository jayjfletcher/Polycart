<?php

declare(strict_types=1);

use Illuminate\Testing\Fluent\AssertableJson;
use RefactorCircus\Polycart\Domains\Cart\Mcp\Tools\ActiveCartTool;
use RefactorCircus\Polycart\Domains\Cart\Mcp\Tools\ClearCartTool;
use RefactorCircus\Polycart\Domains\Cart\Mcp\Tools\ConvertCartTool;
use RefactorCircus\Polycart\Domains\Cart\Mcp\Tools\CreateCartTool;
use RefactorCircus\Polycart\Domains\Cart\Mcp\Tools\DeleteCartTool;
use RefactorCircus\Polycart\Domains\Cart\Mcp\Tools\ListCartsTool;
use RefactorCircus\Polycart\Domains\Cart\Mcp\Tools\MergeCartsTool;
use RefactorCircus\Polycart\Domains\Cart\Mcp\Tools\ShowCartTool;
use RefactorCircus\Polycart\Domains\Cart\Mcp\Tools\TransitionCartTool;
use RefactorCircus\Polycart\Domains\Cart\Mcp\Tools\UpdateCartTool;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\CartLine\Mcp\Tools\AddLinesTool;
use RefactorCircus\Polycart\Domains\CartLine\Mcp\Tools\RemoveLinesTool;
use RefactorCircus\Polycart\Domains\CartLine\Mcp\Tools\UpdateLinesTool;
use RefactorCircus\Polycart\Domains\CartType\Mcp\Tools\ListCartTypesTool;
use RefactorCircus\Polycart\Facades\Polycart;
use RefactorCircus\Polycart\Mcp\PolycartServer;
use Workbench\Database\Factories\UserFactory;

it('lists the cart types', function (): void {
    PolycartServer::tool(ListCartTypesTool::class)
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json): AssertableJson => $json
            ->has('data', 10)
            ->where('data.2.key', 'quote')
            ->where('data.2.converts_to', ['order'])
            ->etc());
});

it('creates, finds and lists carts', function (): void {
    $user = UserFactory::new()->create();

    PolycartServer::tool(CreateCartTool::class, ['type' => 'quote', 'owner_type' => 'user', 'owner_id' => (string) $user->id, 'label' => 'Lobby'])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json): AssertableJson => $json
            ->where('type', 'quote')
            ->where('status', 'draft')
            ->where('label', 'Lobby')
            ->etc());

    PolycartServer::tool(ActiveCartTool::class, ['type' => 'quote', 'owner_type' => 'user', 'owner_id' => (string) $user->id])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json): AssertableJson => $json->where('label', 'Lobby')->etc());

    PolycartServer::tool(ListCartsTool::class, ['type' => 'quote'])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json): AssertableJson => $json->has('data', 1)->etc());

    PolycartServer::tool(ListCartsTool::class, ['type' => 'order'])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json): AssertableJson => $json->has('data', 0)->etc());
});

it('manages lines and totals', function (): void {
    $cart = Polycart::create('cart');
    $product = product(price: 1000);

    PolycartServer::tool(AddLinesTool::class, [
        'cart' => $cart->id,
        'lines' => [
            ['purchasable_type' => 'product', 'purchasable_id' => (string) $product->id, 'quantity' => 3],
        ],
    ])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json): AssertableJson => $json->has('data', 1)->where('data.0.total', 3000)->etc());

    $line = $cart->lines()->firstOrFail();

    PolycartServer::tool(UpdateLinesTool::class, ['cart' => $cart->id, 'lines' => [['id' => $line->id, 'quantity' => 1, 'unit_price' => 900]]])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json): AssertableJson => $json->where('lines.0.total', 900)->where('subtotal', 900)->etc());

    PolycartServer::tool(ShowCartTool::class, ['cart' => $cart->id])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json): AssertableJson => $json->where('subtotal', 900)->has('lines', 1)->etc());

    PolycartServer::tool(RemoveLinesTool::class, ['cart' => $cart->id, 'lines' => [$line->id]])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json): AssertableJson => $json->has('lines', 0)->etc());

    expect($cart->lines()->count())->toBe(0);
});

it('updates, clears and deletes a cart', function (): void {
    $cart = Polycart::create('cart');
    $cart->add(product());

    PolycartServer::tool(UpdateCartTool::class, ['cart' => $cart->id, 'label' => 'Named'])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json): AssertableJson => $json->where('label', 'Named')->etc());

    PolycartServer::tool(ClearCartTool::class, ['cart' => $cart->id])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json): AssertableJson => $json->has('lines', 0)->etc());

    PolycartServer::tool(DeleteCartTool::class, ['cart' => $cart->id])->assertOk();

    expect(CartModel::query()->find($cart->id))->toBeNull();
});

it('transitions, converts and merges', function (): void {
    $cart = Polycart::create('cart', 'guest');
    $cart->add(product());

    $quote = $cart->convertTo('quote');

    PolycartServer::tool(TransitionCartTool::class, ['cart' => $quote->id, 'status' => 'submitted'])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json): AssertableJson => $json->where('status', 'submitted')->etc());

    PolycartServer::tool(ConvertCartTool::class, ['cart' => $quote->id, 'to' => 'order', 'copy' => false])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json): AssertableJson => $json->where('type', 'order')->where('status', 'pending')->etc());

    $mine = Polycart::create('cart');

    PolycartServer::tool(MergeCartsTool::class, ['cart' => $cart->id, 'into' => $mine->id])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json): AssertableJson => $json->where('id', $mine->id)->has('lines', 1)->etc());
});

it('explains a refusal so an agent can correct it', function (): void {
    $quote = Polycart::create('quote');

    PolycartServer::tool(TransitionCartTool::class, ['cart' => $quote->id, 'status' => 'accepted'])
        ->assertHasErrors(['cannot move from [draft] to [accepted]']);

    PolycartServer::tool(ConvertCartTool::class, ['cart' => $quote->id, 'to' => 'saved'])
        ->assertHasErrors(['cannot be converted']);

    PolycartServer::tool(AddLinesTool::class, ['cart' => $quote->id, 'lines' => [['purchasable_type' => 'user', 'purchasable_id' => '1']]])
        ->assertHasErrors(['not listed in polycart.purchasables']);
});

it('answers not found for a missing cart or a line in another cart', function (): void {
    PolycartServer::tool(ShowCartTool::class, ['cart' => 'missing'])->assertHasErrors(['Not found.']);

    $line = Polycart::create('cart')->add(product());

    PolycartServer::tool(RemoveLinesTool::class, ['cart' => Polycart::create('cart')->id, 'lines' => [$line->id]])
        ->assertHasErrors(['Line 0: Line ['.$line->id.'] is not in this cart. (reason: line_not_found)']);
});
