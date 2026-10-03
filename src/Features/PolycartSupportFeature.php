<?php

declare(strict_types=1);

namespace JayI\Polycart\Features;

use JayI\PennantPlus\Domains\Feature\Support\OnLayeredFeature;

/**
 * Switches Polycart in Atrium on and off: its navigation, widgets, search
 * and pages. On until its global value is set. The `SupportFeature` suffix
 * matches PennantPlus's default `gate.global_only` pattern, so only the
 * global value counts and per-user access stays with Polycart's policies.
 *
 * Needs jayi/pennantplus. Point `polycart.atrium.features` at a subclass to
 * change the default, or at your own feature instead.
 */
class PolycartSupportFeature extends OnLayeredFeature {}
