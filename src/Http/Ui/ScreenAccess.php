<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Ui;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Access\Authorizer;

/**
 * Whether the signed-in user may perform an ability, asked the way the JSON
 * API and MCP tools ask it: through the Authorizer and the policies in
 * `polycart.policies`. Controllers refuse with it and views hide controls
 * with it (as `@polycartCan`), so a control is shown exactly when its action
 * is allowed. With `polycart.authorization` off, everything is.
 */
final class ScreenAccess
{
    /**
     * @param  Model|class-string<Model>  $subject
     * @param  array<int, mixed>  $arguments
     */
    public static function allows(string $ability, Model|string $subject, array $arguments = []): bool
    {
        $user = request()->user();

        return self::allowsUser($user instanceof Authenticatable ? $user : null, $ability, $subject, $arguments);
    }

    /**
     * @param  Model|class-string<Model>  $subject
     * @param  array<int, mixed>  $arguments
     */
    public static function allowsUser(?Authenticatable $user, string $ability, Model|string $subject, array $arguments = []): bool
    {
        return app(Authorizer::class)->can($user, $ability, $subject, $arguments);
    }

    /**
     * The model lists act as, or null when authorization is off: lists are
     * limited to the carts that user can access, as the JSON API's are.
     */
    public static function actor(?Authenticatable $user = null): ?Model
    {
        $user ??= request()->user();

        return app(Authorizer::class)->actor($user instanceof Authenticatable ? $user : null);
    }
}
