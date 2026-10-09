<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Atrium\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use RefactorCircus\Polycart\Atrium\ScreenAccess;

/**
 * The same policy checks the JSON API and MCP tools make, for Atrium screens.
 *
 * Polycart's own rather than Atrium's `AuthorizesScreens`, because its checks
 * take arguments (the status to transition to, the type to convert to) and
 * let dashboard operators through; ScreenAccess hands everything else to
 * Atrium's shared check.
 */
trait AuthorizesScreens
{
    /**
     * @param  Model|class-string<Model>  $subject
     * @param  array<int, mixed>  $arguments
     */
    private function authorizeScreen(string $ability, Model|string $subject, array $arguments = []): void
    {
        abort_unless(ScreenAccess::allows($ability, $subject, $arguments), 403);
    }
}
