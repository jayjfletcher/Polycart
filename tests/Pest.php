<?php

declare(strict_types=1);

use RefactorCircus\Polycart\Tests\CortexTestCase;
use RefactorCircus\Polycart\Tests\Fixtures\Models\Organization;
use RefactorCircus\Polycart\Tests\Fixtures\Models\Person;
use RefactorCircus\Polycart\Tests\Fixtures\Models\Product;
use RefactorCircus\Polycart\Tests\Fixtures\Models\Team;
use RefactorCircus\Polycart\Tests\KeenTestCase;
use RefactorCircus\Polycart\Tests\PennantPlusTestCase;
use RefactorCircus\Polycart\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');
uses(CortexTestCase::class)->in('Cortex');
uses(PennantPlusTestCase::class)->in('PennantPlus');
uses(KeenTestCase::class)->in('Keen');

function product(?int $price = 1000, string $sku = 'LOCK-1', ?int $stock = null): Product
{
    return Product::query()->create(['sku' => $sku, 'price' => $price, 'stock' => $stock]);
}

function organization(string $name = 'Acme'): Organization
{
    return Organization::query()->create(['name' => $name]);
}

function team(Organization $organization, string $name = 'Team'): Team
{
    return Team::query()->create(['organization_id' => $organization->id, 'name' => $name]);
}

function person(Team ...$teams): Person
{
    $person = Person::query()->create(['name' => 'Person']);
    $person->teams()->attach(array_map(fn (Team $team): int => $team->id, $teams));

    return $person;
}

/**
 * Actions no surface calls directly, and why.
 *
 * @var array<string, string>
 */
const INTERNAL_ACTIONS = [
    // The API and MCP only add in batches; each line in a batch goes through
    // this action, as does $cart->add() in code.
    'AddLineAction' => 'Reached through AddLinesAction.',
    'UpdateLineAction' => 'Reached through UpdateLinesAction.',
    'RemoveLineAction' => 'Reached through RemoveLinesAction.',
];

/**
 * Actions whose name appears in no class under the given source directory.
 *
 * Every use case is reachable from both the JSON API and the MCP server, so
 * this is empty for both surfaces. A new Action without a request on each
 * side fails the arch test rather than shipping half-exposed.
 *
 * @return array<int, string>
 */
function parityGaps(string $directory): array
{
    $actions = array_map(
        fn (string $path): string => basename($path, '.php'),
        (array) glob(dirname(__DIR__).'/src/Domains/*/Actions/*.php'),
    );

    $surface = implode("\n", array_map(
        fn (string $path): string => (string) file_get_contents($path),
        (array) glob(dirname(__DIR__).'/src/Domains/*/'.$directory.'/*.php'),
    ));

    return array_values(array_filter(
        $actions,
        fn (string $action): bool => ! array_key_exists($action, INTERNAL_ACTIONS)
            && ! preg_match('/\b'.$action.'\b/', $surface),
    ));
}
