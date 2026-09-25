<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use JayI\Polycart\Facades\Polycart;
use JayI\Polycart\Mcp\PolycartServer;
use JayI\Polycart\Mcp\Tools\AddLinesTool;
use JayI\Polycart\Mcp\Tools\RemoveLinesTool;
use JayI\Polycart\Mcp\Tools\ShowCartTool;
use JayI\Polycart\Mcp\Tools\UpdateCartTool;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartActivity;
use JayI\Polycart\Models\CartLine;
use JayI\Polycart\Models\CartMember;
use JayI\Polycart\Policies\CartActivityPolicy;
use JayI\Polycart\Policies\CartLinePolicy;
use JayI\Polycart\Policies\CartMemberPolicy;
use JayI\Polycart\Policies\CartPolicy;
use JayI\Polycart\PolycartServiceProvider;
use JayI\Polycart\Tests\Fixtures\Models\Quote;
use JayI\Polycart\Tests\Fixtures\Policies\KeepLinesPolicy;
use JayI\Polycart\Tests\Fixtures\Policies\ReadOnlyCartPolicy;

beforeEach(function (): void {
    config()->set('polycart.authorization', true);

    $this->sales = team(organization(), 'Sales');
    $this->ann = person($this->sales);
    $this->bob = person($this->sales);
});

/**
 * Register the policies again after a test changes `polycart.policies`, as
 * the provider does on boot.
 *
 * @param  array<class-string, class-string>  $policies
 */
function usePolicies(array $policies): void
{
    foreach ($policies as $model => $policy) {
        config()->set('polycart.policies.'.$model, $policy);
    }

    $provider = app()->getProvider(PolycartServiceProvider::class);

    (fn () => $this->registerPolicies())->call($provider);
}

it('registers the policies from the config', function (): void {
    expect(Gate::getPolicyFor(Cart::class))->toBeInstanceOf(CartPolicy::class)
        ->and(Gate::getPolicyFor(Quote::class))->toBeInstanceOf(CartPolicy::class)
        ->and(Gate::getPolicyFor(CartLine::class))->toBeInstanceOf(CartLinePolicy::class)
        ->and(Gate::getPolicyFor(CartMember::class))->toBeInstanceOf(CartMemberPolicy::class)
        ->and(Gate::getPolicyFor(CartActivity::class))->toBeInstanceOf(CartActivityPolicy::class);
});

it('lets the owner do anything with their cart', function (): void {
    $cart = Polycart::create('cart', $this->ann, scope: $this->sales);

    // Ownership is enough on its own, without the owner's membership.
    $cart->members()->delete();
    $cart->unsetRelation('members');

    expect($this->ann->can('view', $cart))->toBeTrue()
        ->and($this->ann->can('update', $cart))->toBeTrue()
        ->and($this->ann->can('delete', $cart))->toBeTrue()
        ->and($this->ann->can('approve', $cart))->toBeTrue()
        ->and($this->bob->can('view', $cart))->toBeFalse()
        ->and($this->bob->can('approve', $cart))->toBeFalse();
});

it('checks lines, members and activity against their cart', function (): void {
    $cart = Polycart::create('cart', $this->ann, scope: $this->sales);
    $line = $cart->add(product());
    Polycart::share($cart, $this->bob, 'viewer');
    $member = $cart->members()->where('member_id', (string) $this->bob->id)->firstOrFail();

    expect($this->bob->can('viewAny', [CartLine::class, $cart]))->toBeTrue()
        ->and($this->bob->can('view', $line))->toBeTrue()
        ->and($this->bob->can('create', [CartLine::class, $cart]))->toBeFalse()
        ->and($this->bob->can('update', $line))->toBeFalse()
        ->and($this->bob->can('delete', $line))->toBeFalse()
        ->and($this->bob->can('viewAny', [CartMember::class, $cart]))->toBeTrue()
        ->and($this->bob->can('delete', $member))->toBeFalse()
        ->and($this->bob->can('viewAny', [CartActivity::class, $cart]))->toBeTrue()
        ->and($this->ann->can('update', $line))->toBeTrue()
        ->and($this->ann->can('create', [CartMember::class, $cart]))->toBeTrue()
        ->and($this->ann->can('delete', $member))->toBeTrue()
        ->and($this->ann->can('update', $cart->activities()->firstOrFail()))->toBeFalse();
});

it('uses a cart policy swapped in the config, for carts and their lines', function (): void {
    usePolicies([Cart::class => ReadOnlyCartPolicy::class]);

    $cart = Polycart::create('cart', $this->ann, scope: $this->sales);

    $this->actingAs($this->ann)->getJson("/polycart/carts/{$cart->id}")->assertOk();
    $this->actingAs($this->ann)->patchJson("/polycart/carts/{$cart->id}", ['name' => 'Renamed'])->assertForbidden();
    $this->actingAs($this->ann)->postJson("/polycart/carts/{$cart->id}/lines", ['lines' => [['unit_price' => 100]]])->assertForbidden();

    PolycartServer::actingAs($this->ann)->tool(ShowCartTool::class, ['cart' => $cart->id])->assertOk();
    PolycartServer::actingAs($this->ann)->tool(UpdateCartTool::class, ['cart' => $cart->id, 'name' => 'Renamed'])->assertHasErrors(['Unauthorized.']);
    PolycartServer::actingAs($this->ann)->tool(AddLinesTool::class, ['cart' => $cart->id, 'lines' => [['unit_price' => 100]]])->assertHasErrors(['Unauthorized.']);
});

it('checks each named line against a line policy swapped in the config', function (): void {
    usePolicies([CartLine::class => KeepLinesPolicy::class]);

    $cart = Polycart::create('cart', $this->ann, scope: $this->sales);
    $line = $cart->add(product());

    $this->actingAs($this->ann)
        ->patchJson("/polycart/carts/{$cart->id}/lines", ['lines' => [['id' => $line->id, 'quantity' => 3]]])
        ->assertOk();

    $this->actingAs($this->ann)
        ->deleteJson("/polycart/carts/{$cart->id}/lines", ['lines' => [$line->id]])
        ->assertForbidden();

    PolycartServer::actingAs($this->ann)
        ->tool(RemoveLinesTool::class, ['cart' => $cart->id, 'lines' => [$line->id]])
        ->assertHasErrors(['Unauthorized.']);

    expect($cart->lines()->count())->toBe(1);
});
