<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;
use RefactorCircus\Atrium\Domains\Navigation\Data\NavItem;
use RefactorCircus\Atrium\Domains\Navigation\Services\NavigationRegistry;
use RefactorCircus\Polycart\Atrium\Features\PolycartSupportFeature;
use RefactorCircus\Polycart\Atrium\PolycartPlugin;
use RefactorCircus\Polycart\Tests\Fixtures\Features\UninstalledFeature;
use RefactorCircus\Polycart\Tests\Fixtures\Models\Person;

beforeEach(function (): void {
    // Atrium denies access outside local until a gate is defined.
    app()->detectEnvironment(fn (): string => 'local');
});

/**
 * @return array<int, string>
 */
function navigationFor(?Authenticatable $user = null): array
{
    $request = Request::create('/atrium');
    $request->setUserResolver(fn (): ?Authenticatable => $user);

    return array_map(fn (NavItem $item): string => $item->label, app(NavigationRegistry::class)->items($request));
}

function signedIn(): Person
{
    return person(team(organization()));
}

/**
 * Off until its global value is set, to show the class can be overridden.
 */
class OffPolycartSupportFeature extends PolycartSupportFeature
{
    protected function default(): bool
    {
        return false;
    }
}

it('gates polycart on the bundled feature by default', function (): void {
    expect(app(PolycartPlugin::class)->features())->toBe([PolycartSupportFeature::class]);
});

it('shows polycart until the feature is turned off globally', function (): void {
    $user = signedIn();

    expect(navigationFor($user))->toContain('Carts');

    $this->actingAs($user)->get(route('atrium.polycart.carts.index'))->assertOk();

    Feature::for(null)->deactivate(PolycartSupportFeature::class);

    expect(navigationFor($user))->not->toContain('Carts')->not->toContain('Cart types');

    $this->actingAs($user)->get(route('atrium.polycart.carts.index'))->assertNotFound();
    $this->actingAs($user)->get(route('atrium.polycart.types.index'))->assertNotFound();
});

it('only counts the global value, leaving per-user access to the policies', function (): void {
    $user = signedIn();

    Feature::for($user)->deactivate(PolycartSupportFeature::class);

    expect(navigationFor($user))->toContain('Carts');

    $this->actingAs($user)->get(route('atrium.polycart.carts.index'))->assertOk();
});

it('uses a subclass named in the config instead', function (): void {
    config()->set('polycart.atrium.features', [OffPolycartSupportFeature::class]);

    expect(navigationFor(signedIn()))->not->toContain('Carts');

    Feature::for(null)->activate(OffPolycartSupportFeature::class);

    expect(navigationFor(signedIn()))->toContain('Carts');
});

it('skips a feature class that cannot be loaded', function (): void {
    config()->set('polycart.atrium.features', [UninstalledFeature::class, 'Missing\\Feature', 'polycart-dashboard']);

    expect(app(PolycartPlugin::class)->features())->toBe(['polycart-dashboard']);
});
