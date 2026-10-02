<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use JayI\Atrium\Navigation\NavigationRegistry;
use JayI\Atrium\Navigation\NavItem;
use JayI\Polycart\Atrium\PolycartPlugin;
use JayI\Polycart\Facades\Polycart;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Policies\CartPolicy;
use JayI\Polycart\Tests\Fixtures\Models\Person;
use JayI\Polycart\Tests\Fixtures\Models\Team;

/**
 * The dashboard asks the same policy questions the JSON API asks: each
 * control is shown exactly when its action would be allowed, and the action
 * itself is refused otherwise.
 */
final class NoListingCartPolicy extends CartPolicy
{
    public function viewAny(Model $user): bool
    {
        return false;
    }
}

beforeEach(function (): void {
    ValidateCsrfToken::except(['*']);

    // Atrium denies access outside local until a gate is defined.
    app()->detectEnvironment(fn (): string => 'local');

    config()->set('polycart.authorization', true);

    $this->sales = team(organization(), 'Sales');
    $this->ann = person($this->sales);
    $this->cart = Polycart::create('cart', $this->ann, scope: $this->sales, attributes: ['label' => 'Lobby']);
    $this->line = $this->cart->add(product(), 2);
});

/**
 * @return array<int, string>
 */
function polycartNavigation(?Authenticatable $user = null): array
{
    $request = Request::create('/atrium');
    $request->setUserResolver(fn (): ?Authenticatable => $user);

    return array_map(fn (NavItem $item): string => $item->label, app(NavigationRegistry::class)->items($request));
}

function testId(string $id): string
{
    return 'data-testid="'.$id.'"';
}

/**
 * Someone in the cart's team, shared in with a role, or not at all.
 */
function member(Team $team, Cart $cart, ?string $role = null): Person
{
    $person = person($team);

    if ($role !== null) {
        Polycart::share($cart, $person, $role);
    }

    return $person;
}

const CART_CONTROLS = ['clear-cart', 'delete-cart', 'update-line', 'remove-line', 'set-visibility', 'remove-member', 'share-cart', 'details-card'];

it('shows the navigation to users who may list carts, with icons', function (): void {
    $items = array_filter(
        app(NavigationRegistry::class)->items(tap(Request::create('/atrium'), fn (Request $request) => $request->setUserResolver(fn (): Person => $this->ann))),
        fn (NavItem $item): bool => $item->group === 'Polycart',
    );

    expect(array_map(fn (NavItem $item): string => $item->label, array_values($items)))->toBe(['Carts', 'Cart types'])
        ->and(array_filter(array_map(fn (NavItem $item): ?string => $item->icon, $items)))->toHaveCount(2);
});

it('hides the navigation, widgets and search, and refuses the pages, without viewAny', function (): void {
    Gate::policy(Cart::class, NoListingCartPolicy::class);

    expect(polycartNavigation($this->ann))->not->toContain('Carts')->not->toContain('Cart types');

    $request = Request::create('/atrium');
    $request->setUserResolver(fn (): Person => $this->ann);

    $plugin = app(PolycartPlugin::class);

    expect(array_filter($plugin->widgets(), fn ($widget): bool => $widget->isAuthorized($request)))->toBe([])
        ->and($plugin->search()?->isAuthorized($request))->toBeFalse();

    $this->actingAs($this->ann)->get(route('atrium.polycart.carts.index'))->assertForbidden();
    $this->actingAs($this->ann)->get(route('atrium.polycart.types.index'))->assertForbidden();
});

it('hides everything from a guest when authorization is on', function (): void {
    expect(polycartNavigation())->not->toContain('Carts');

    $this->get(route('atrium.polycart.carts.index'))->assertForbidden();
    $this->get(route('atrium.polycart.carts.show', $this->cart))->assertForbidden();
});

it('lists only the carts the user can access, as the JSON API does', function (): void {
    $outsider = member($this->sales, $this->cart);
    Polycart::create('cart', $outsider, scope: $this->sales, attributes: ['label' => 'Theirs']);

    $this->actingAs($this->ann)->get(route('atrium.polycart.carts.index'))
        ->assertOk()
        ->assertSee('Lobby')
        ->assertDontSee('Theirs');

    expect(app(PolycartPlugin::class)->search()?->results('Theirs'))->toBe([]);
});

it('refuses a cart and every change to it to someone with no role', function (): void {
    $outsider = member($this->sales, $this->cart);
    $owner = $this->cart->members()->firstOrFail();

    $this->actingAs($outsider);

    $this->get(route('atrium.polycart.carts.show', $this->cart))->assertForbidden();
    $this->patch(route('atrium.polycart.carts.update', $this->cart), ['label' => 'x'])->assertForbidden();
    $this->post(route('atrium.polycart.carts.clear', $this->cart))->assertForbidden();
    $this->delete(route('atrium.polycart.carts.destroy', $this->cart))->assertForbidden();
    $this->patch(route('atrium.polycart.carts.lines.update', [$this->cart, $this->line]), ['quantity' => 9])->assertForbidden();
    $this->delete(route('atrium.polycart.carts.lines.destroy', [$this->cart, $this->line]))->assertForbidden();
    $this->put(route('atrium.polycart.carts.visibility', $this->cart), ['visibility' => 'scope'])->assertForbidden();
    $this->post(route('atrium.polycart.carts.members.store', $this->cart), ['member_type' => 'person', 'member_id' => (string) $outsider->id, 'role' => 'owner'])->assertForbidden();
    $this->delete(route('atrium.polycart.carts.members.destroy', [$this->cart, $owner]))->assertForbidden();

    expect($this->cart->fresh()?->label)->toBe('Lobby')
        ->and($this->line->fresh()?->quantity)->toBe(2)
        ->and($this->cart->members()->count())->toBe(1);
});

it('shows a viewer the cart without any control', function (): void {
    $viewer = member($this->sales, $this->cart, 'viewer');

    $response = $this->actingAs($viewer)->get(route('atrium.polycart.carts.show', $this->cart))
        ->assertOk()
        ->assertSee(testId('activity-card'), false);

    foreach (CART_CONTROLS as $control) {
        $response->assertDontSee(testId($control), false);
    }

    $this->post(route('atrium.polycart.carts.clear', $this->cart))->assertForbidden();
    $this->patch(route('atrium.polycart.carts.lines.update', [$this->cart, $this->line]), ['quantity' => 9])->assertForbidden();
    expect($this->line->fresh()?->quantity)->toBe(2);
});

it('shows an editor the controls for changing content only', function (): void {
    $editor = member($this->sales, $this->cart, 'editor');

    $this->actingAs($editor)->get(route('atrium.polycart.carts.show', $this->cart))
        ->assertOk()
        ->assertSee(testId('clear-cart'), false)
        ->assertSee(testId('update-line'), false)
        ->assertSee(testId('remove-line'), false)
        ->assertSee(testId('details-card'), false)
        ->assertDontSee(testId('delete-cart'), false)
        ->assertDontSee(testId('set-visibility'), false)
        ->assertDontSee(testId('remove-member'), false)
        ->assertDontSee(testId('share-cart'), false);

    $this->patch(route('atrium.polycart.carts.lines.update', [$this->cart, $this->line]), ['quantity' => 5])->assertSessionHas('status');
    expect($this->line->fresh()?->quantity)->toBe(5);

    $this->delete(route('atrium.polycart.carts.destroy', $this->cart))->assertForbidden();
    $this->put(route('atrium.polycart.carts.visibility', $this->cart), ['visibility' => 'scope'])->assertForbidden();
    expect(Cart::query()->find($this->cart->id))->not->toBeNull();
});

it('shows the owner every control', function (): void {
    $response = $this->actingAs($this->ann)->get(route('atrium.polycart.carts.show', $this->cart))->assertOk();

    foreach (CART_CONTROLS as $control) {
        $response->assertSee(testId($control), false);
    }

    $this->delete(route('atrium.polycart.carts.destroy', $this->cart))->assertRedirect(route('atrium.polycart.carts.index'));
    expect(Cart::query()->find($this->cart->id))->toBeNull();
});

it('offers only the moves the viewer may make, and refuses the others', function (): void {
    $order = Polycart::create('order', $this->ann, scope: $this->sales);
    $buyer = member($this->sales, $order, 'buyer');

    // Moving to processing needs `checkout`, which a buyer has; cancelling
    // needs `transition`, which they lack.
    $this->actingAs($buyer)->get(route('atrium.polycart.carts.show', $order))
        ->assertOk()
        ->assertSee(testId('lifecycle-card'), false)
        ->assertSee(testId('transition-cart'), false)
        ->assertSee('value="processing"', false)
        ->assertDontSee('value="cancelled"', false)
        ->assertDontSee(testId('convert-cart'), false);

    $this->post(route('atrium.polycart.carts.transition', $order), ['status' => 'cancelled'])->assertForbidden();
    $this->post(route('atrium.polycart.carts.convert', $order), ['to' => 'cart'])->assertForbidden();
    $this->post(route('atrium.polycart.carts.transition', $order), ['status' => 'processing'])->assertSessionHas('status');

    expect($order->fresh()?->status)->toBe('processing');

    $viewer = member($this->sales, $order, 'viewer');

    $this->actingAs($viewer)->get(route('atrium.polycart.carts.show', $order))
        ->assertOk()
        ->assertDontSee(testId('lifecycle-card'), false);
});

it('shows statuses as dots coloured by meaning', function (): void {
    $order = Polycart::create('order', $this->ann, scope: $this->sales, attributes: ['label' => 'Pending order']);

    $this->actingAs($this->ann)->get(route('atrium.polycart.carts.index'))
        ->assertOk()
        ->assertSee('data-status="pending"', false)
        ->assertSee('bg-info', false);

    $this->actingAs($this->ann)->get(route('atrium.polycart.carts.show', $order))
        ->assertOk()
        ->assertSee('data-status="pending"', false);
});

it('shows every control and allows every action with authorization off', function (): void {
    config()->set('polycart.authorization', false);

    expect(polycartNavigation())->toContain('Carts');

    $response = $this->get(route('atrium.polycart.carts.show', $this->cart))->assertOk();

    foreach (CART_CONTROLS as $control) {
        $response->assertSee(testId($control), false);
    }

    $this->post(route('atrium.polycart.carts.clear', $this->cart))->assertSessionHas('status');
    expect($this->cart->lines()->count())->toBe(0);
});
