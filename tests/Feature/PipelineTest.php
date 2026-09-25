<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use JayI\Polycart\Events\Action\LineAddedActionEvent;
use JayI\Polycart\Exceptions\LineRejectedException;
use JayI\Polycart\Facades\Polycart;
use JayI\Polycart\Mcp\PolycartServer;
use JayI\Polycart\Mcp\Tools\AddLinesTool;
use JayI\Polycart\Models\CartLine;
use JayI\Polycart\Pipeline\Stages\BuildLine;
use JayI\Polycart\Pipeline\Stages\CheckAccepted;
use JayI\Polycart\Pipeline\Stages\FindMatchingLine;
use JayI\Polycart\Pipeline\Stages\PrepareLine;
use JayI\Polycart\Pipeline\Stages\ResolvePrice;
use JayI\Polycart\Pipeline\Stages\ValidateLine;
use JayI\Polycart\Pipeline\Stages\WriteLine;
use JayI\Polycart\Tests\Fixtures\Models\Person;
use JayI\Polycart\Tests\Fixtures\Stages\ApplyBulkDiscount;
use JayI\Polycart\Tests\Fixtures\Stages\CheckStock;
use JayI\Polycart\Tests\Fixtures\Stages\EnsureCustomerCanBuy;
use JayI\Polycart\Types\CartTypeRegistry;

it('sends lines through the default stages in order', function (): void {
    expect(app(CartTypeRegistry::class)->get('cart')->addLineStages())->toBe([
        PrepareLine::class,
        CheckAccepted::class,
        FindMatchingLine::class,
        ResolvePrice::class,
        BuildLine::class,
        ValidateLine::class,
        WriteLine::class,
    ]);
});

it('lets a type slot its own stages into the defaults', function (): void {
    $stages = app(CartTypeRegistry::class)->get('wholesale')->addLineStages();

    expect(array_slice($stages, 1))->toBe([
        PrepareLine::class,
        CheckAccepted::class,
        EnsureCustomerCanBuy::class,
        FindMatchingLine::class,
        ResolvePrice::class,
        ApplyBulkDiscount::class.':10,20',
        BuildLine::class,
        CheckStock::class,
        ValidateLine::class,
        WriteLine::class,
    ])->and($stages[0])->toBeInstanceOf(Closure::class);
});

it('applies a configured stage to the price', function (): void {
    $cart = Polycart::create('wholesale');

    $small = $cart->add(product(price: 1000, sku: 'A'), 5);
    $bulk = $cart->add(product(price: 1000, sku: 'B'), 10);

    expect($small->unit_price)->toBe(1000)
        ->and($bulk->unit_price)->toBe(800);
});

it('runs closure stages', function (): void {
    $line = Polycart::usingSource('pos', fn (): CartLine => Polycart::create('wholesale')->add(product()));

    expect($line->meta)->toBe(['added_via' => 'pos']);
});

it('refuses a line with a reason and writes nothing', function (): void {
    $cart = Polycart::create('wholesale');
    $product = product(stock: 3);

    $cart->add($product, 2);

    try {
        $cart->add($product, 2);
        $this->fail('The line should have been refused.');
    } catch (LineRejectedException $e) {
        expect($e->getMessage())->toBe('Only 3 left.')
            ->and($e->reason)->toBe('out_of_stock');
    }

    // The merge into the existing line was undone with the rest.
    expect($cart->lines()->sole()->quantity)->toBe(2)
        ->and($cart->activities()->where('action', 'line_updated')->count())->toBe(0);
});

it('gives stages the signed-in customer', function (): void {
    $this->actingAs(Person::query()->create(['name' => 'Blocked']));

    Polycart::create('wholesale')->add(product());
})->throws(LineRejectedException::class, 'This customer cannot buy.');

it('undoes the write when a later stage refuses', function (): void {
    $cart = Polycart::create('audited');

    expect(fn (): CartLine => $cart->add(product(), meta: ['fail_after_write' => true]))
        ->toThrow(LineRejectedException::class, 'The audit refused it.');

    expect($cart->lines()->count())->toBe(0);
});

it('refuses a line a stage dropped without saying why', function (): void {
    Polycart::create('careless')->add(product());
})->throws(LineRejectedException::class, 'without writing it');

it('keeps the package\'s own refusals coded', function (): void {
    try {
        Polycart::create('quote')->add(product(price: null));
    } catch (LineRejectedException $e) {
        expect($e->reason)->toBe('missing_price');
    }

    try {
        Polycart::create('cart')->add(product(), 0);
    } catch (LineRejectedException $e) {
        expect($e->reason)->toBe('invalid_quantity');
    }
});

it('runs the pipeline for lines added over the API and MCP', function (): void {
    $cart = Polycart::create('wholesale');
    $product = product(stock: 1);

    $line = ['purchasable_type' => 'product', 'purchasable_id' => (string) $product->id, 'quantity' => 2];

    $this->postJson("/polycart/carts/{$cart->id}/lines", ['lines' => [$line]])
        ->assertUnprocessable()
        ->assertExactJson(['message' => 'Line 0: Only 1 left.', 'reason' => 'out_of_stock', 'line' => 0]);

    PolycartServer::tool(AddLinesTool::class, ['cart' => $cart->id, 'lines' => [$line]])
        ->assertHasErrors(['Line 0: Only 1 left. (reason: out_of_stock)']);

    $this->postJson("/polycart/carts/{$cart->id}/lines", ['lines' => [['purchasable_type' => 'product', 'purchasable_id' => (string) $product->id]]])
        ->assertCreated()
        ->assertJsonPath('data.0.meta.added_via', 'api');
});

it('runs the pipeline when carts merge', function (): void {
    $product = product(stock: 3);

    $guest = Polycart::create('wholesale', 'session-1');
    $guest->add($product, 2);

    $mine = Polycart::create('wholesale');
    $mine->add($product, 2);

    Polycart::merge($guest, $mine);
})->throws(LineRejectedException::class, 'Only 3 left.');

it('adds several lines at once, all or nothing', function (): void {
    Event::fake([LineAddedActionEvent::class]);

    $cart = Polycart::create('wholesale');
    $plenty = product(sku: 'A', stock: 50);
    $scarce = product(sku: 'B', stock: 1);

    $lines = $cart->addLines([
        ['purchasable' => $plenty, 'quantity' => 12],
        ['purchasable' => null, 'meta' => ['description' => 'Delivery'], 'unit_price' => 2500],
    ]);

    expect($lines)->toHaveCount(2)
        ->and($lines->first()?->unit_price)->toBe(800);

    try {
        $cart->addLines([
            ['purchasable' => $plenty, 'quantity' => 1],
            ['purchasable' => $scarce, 'quantity' => 2],
        ]);
        $this->fail('The batch should have been refused.');
    } catch (LineRejectedException $e) {
        expect($e->index)->toBe(1)
            ->and($e->reason)->toBe('out_of_stock')
            ->and($e->getMessage())->toBe('Line 1: Only 1 left.');
    }

    // Nothing from the refused batch stuck, and no one heard about it.
    expect($cart->lines()->pluck('quantity')->all())->toBe([12, 1])
        ->and($cart->activities()->count())->toBe(3);

    Event::assertDispatchedTimes(LineAddedActionEvent::class, 2);
});
