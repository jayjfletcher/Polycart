<?php

declare(strict_types=1);

use RefactorCircus\Atrium\Support\Icons;
use RefactorCircus\Polycart\Atrium\PolycartPlugin;

it('gives its sidebar section its own icon', function (): void {
    [$group] = app(PolycartPlugin::class)->navigationGroups();

    expect($group->icon)->toBe(Icons::svg('shopping-cart'))
        ->and($group->sort)->toBe(30);
});

it('collects its pages in that section', function (): void {
    $plugin = app(PolycartPlugin::class);
    [$group] = $plugin->navigationGroups();

    $labels = array_values(array_unique(array_map(fn ($item): ?string => $item->group, $plugin->navigation())));

    expect($labels)->toBe([$group->name]);
});
