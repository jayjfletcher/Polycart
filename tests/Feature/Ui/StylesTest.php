<?php

declare(strict_types=1);

use RefactorCircus\Atrium\Testing\AtriumStyles;

/*
 * Polycart ships no stylesheet: every class its views use must be one
 * Atrium's compiled stylesheet contains, and no view styles itself.
 */
it('uses only classes Atrium\'s stylesheet contains', function (): void {
    expect(AtriumStyles::missingClasses(dirname(__DIR__, 3).'/resources/views'))->toBe([]);
});

it('never styles a view inline', function (): void {
    expect(AtriumStyles::inlineStyles(dirname(__DIR__, 3).'/resources/views'))->toBe([]);
});
