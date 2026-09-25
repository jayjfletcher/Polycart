<?php

declare(strict_types=1);

use JayI\Polycart\Exceptions\InvalidParentException;
use JayI\Polycart\Facades\Polycart;

it('nests carts and records the root of the tree', function (): void {
    $project = Polycart::create('project', attributes: ['label' => 'Tower A']);
    $set = Polycart::create('set', attributes: ['parent_id' => $project->id]);
    $opening = Polycart::create('opening', attributes: ['parent_id' => $set->id]);

    expect($set->root_id)->toBe($project->id)
        ->and($opening->root_id)->toBe($project->id)
        ->and($opening->parent?->is($set))->toBeTrue()
        ->and($opening->root?->is($project))->toBeTrue()
        ->and($project->children()->pluck('id')->all())->toBe([$set->id])
        ->and($project->descendants()->count())->toBe(2);
});

it('refuses a parent of the wrong type', function (): void {
    $project = Polycart::create('project');

    Polycart::create('opening', attributes: ['parent_id' => $project->id]);
})->throws(InvalidParentException::class, 'A [opening] cart cannot be nested under a [project] cart.');

it('refuses a type that must be nested without a parent', function (): void {
    Polycart::create('set');
})->throws(InvalidParentException::class, 'must be nested');

it('keeps a root type at the root', function (): void {
    $other = Polycart::create('project');

    Polycart::create('project', attributes: ['parent_id' => $other->id]);
})->throws(InvalidParentException::class);
