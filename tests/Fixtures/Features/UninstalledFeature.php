<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Tests\Fixtures\Features;

use RefactorCircus\Polycart\Tests\Fixtures\Features\Missing\LayeredFeature;

/**
 * A feature whose parent is not installed, as PolycartSupportFeature is
 * without refactor-circus/pennantplus: loading it fails, so the plugin must skip it.
 */
final class UninstalledFeature extends LayeredFeature {}
