<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Ui;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use JayI\Polycart\Access\Authorizer;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartActivity;
use JayI\Polycart\Models\CartLine;
use JayI\Polycart\Models\CartMember;

/**
 * Whether the signed-in user may perform an ability, asked the way the JSON
 * API and MCP tools ask it: through the Authorizer and the policies in
 * `polycart.policies`. Controllers refuse with it and views hide controls
 * with it (as `@polycartCan`), so a control is shown exactly when its action
 * is allowed. With `polycart.authorization` off, everything is.
 *
 * An operator (`polycart.atrium.show_all`) is allowed everything on
 * Polycart's own models and lists every cart, on these screens only: the
 * JSON API and MCP tools never consult this class.
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
        if (self::ownModel($subject) && self::operator($user)) {
            return true;
        }

        return app(Authorizer::class)->can($user, $ability, $subject, $arguments);
    }

    /**
     * Whether the user runs the dashboard: with `polycart.atrium.show_all`
     * true, every signed-in user who reaches Atrium; with a Gate ability's
     * name, those it allows. Operators see every cart and may take every
     * action on it here, whatever their role on it.
     */
    public static function operator(?Authenticatable $user): bool
    {
        if ($user === null) {
            return false;
        }

        $showAll = config('polycart.atrium.show_all', false);

        if ($showAll === true) {
            return true;
        }

        return is_string($showAll) && $showAll !== '' && Gate::forUser($user)->allows($showAll);
    }

    /**
     * The model lists act as, or null when authorization is off or the user
     * is an operator: lists are limited to the carts that user can access,
     * as the JSON API's are, unless they may see every cart.
     */
    public static function actor(?Authenticatable $user = null): ?Model
    {
        $user ??= request()->user();
        $user = $user instanceof Authenticatable ? $user : null;

        return self::operator($user) ? null : app(Authorizer::class)->actor($user);
    }

    /**
     * Operators are allowed everything on Polycart's models, never on others.
     *
     * @param  Model|class-string<Model>  $subject
     */
    private static function ownModel(Model|string $subject): bool
    {
        foreach ([Cart::class, CartLine::class, CartMember::class, CartActivity::class] as $model) {
            if (is_a($subject, $model, true)) {
                return true;
            }
        }

        return false;
    }
}
