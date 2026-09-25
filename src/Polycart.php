<?php

declare(strict_types=1);

namespace JayI\Polycart;

use BackedEnum;
use Closure;
use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Actions\ActiveCartAction;
use JayI\Polycart\Actions\ConvertCartAction;
use JayI\Polycart\Actions\CreateCartAction;
use JayI\Polycart\Actions\MergeCartsAction;
use JayI\Polycart\Actions\ShareCartAction;
use JayI\Polycart\Actions\UnshareCartAction;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartMember;
use JayI\Polycart\Support\SourceContext;
use JayI\Polycart\Types\CartType;
use JayI\Polycart\Types\CartTypeRegistry;

/**
 * The public entry point.
 *
 * An owner is either a model — a user, a team, a customer — or a string
 * session key for a guest who has not signed in.
 */
final class Polycart
{
    public function __construct(
        private readonly CartTypeRegistry $types,
        private readonly CreateCartAction $creator,
        private readonly ActiveCartAction $activator,
        private readonly ConvertCartAction $converter,
        private readonly MergeCartsAction $merger,
    ) {}

    public function types(): CartTypeRegistry
    {
        return $this->types;
    }

    public function type(string $key): CartType
    {
        return $this->types->get($key);
    }

    /**
     * Start a new cart of a type, hydrated as that type's model.
     *
     * @param  array<string, mixed>  $attributes  Any of label, meta, parent_id.
     * @param  Model|null  $scope  The level of the tree it lives in, such as a team.
     */
    public function create(string $type, Model|string|null $owner = null, array $attributes = [], ?Model $scope = null): Cart
    {
        return $this->creator->execute($type, $owner, $attributes, $scope);
    }

    /**
     * The owner's current cart of a type, started if they have none.
     *
     * A cart is current while it has not expired and is still in its type's
     * initial status. Once it moves on — checked out, submitted — the next
     * call starts a fresh one. Each scope has its own active cart.
     */
    public function active(string $type, Model|string $owner, ?Model $scope = null): Cart
    {
        return $this->activator->execute($type, $owner, $scope);
    }

    /**
     * Stamp every cart created inside the callback with a source.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function usingSource(BackedEnum|string $source, Closure $callback): mixed
    {
        return app(SourceContext::class)->using($source, $callback);
    }

    /**
     * Share a cart with someone, or a whole scope such as a team, inside the
     * cart's boundary.
     */
    public function share(Cart $cart, Model $member, string $role): CartMember
    {
        return app(ShareCartAction::class)->execute($cart, $member, $role);
    }

    public function unshare(Cart $cart, Model $member): void
    {
        $membership = $cart->members()
            ->where('member_type', $member->getMorphClass())
            ->where('member_id', (string) $member->getKey())
            ->firstOrFail();

        app(UnshareCartAction::class)->execute($cart, $membership);
    }

    /**
     * Find a cart of any type, hydrated as its type's model.
     */
    public function find(string $id): ?Cart
    {
        return Cart::query()->find($id);
    }

    /**
     * Turn a cart into another type, copying it unless `copy` is false.
     */
    public function convert(Cart $cart, string $to, bool $copy = true): Cart
    {
        return $this->converter->execute($cart, $to, $copy);
    }

    /**
     * Move every line of one cart into another and delete the first.
     */
    public function merge(Cart $from, Cart $into): Cart
    {
        return $this->merger->execute($from, $into);
    }
}
