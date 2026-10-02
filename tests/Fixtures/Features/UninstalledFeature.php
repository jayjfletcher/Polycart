<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Features;

use JayI\Polycart\Tests\Fixtures\Features\Missing\LayeredFeature;

/**
 * A feature whose parent is not installed, as PolycartSupportFeature is
 * without jayi/pennantplus: loading it fails, so the plugin must skip it.
 */
final class UninstalledFeature extends LayeredFeature {}
