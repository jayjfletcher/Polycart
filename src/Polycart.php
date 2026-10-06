<?php

declare(strict_types=1);

namespace JayI\Polycart;

use BackedEnum;
use Closure;
use Illuminate\Database\Eloquent\Model;
use JayI\Foundation\Support\Surface;
use JayI\Polycart\Domains\Cart\Actions\ActiveCartAction;
use JayI\Polycart\Domains\Cart\Actions\ConvertCartAction;
use JayI\Polycart\Domains\Cart\Actions\CreateCartAction;
use JayI\Polycart\Domains\Cart\Actions\MergeCartsAction;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartType\Services\CartTypeRegistry;
use JayI\Polycart\Domains\CartType\Support\CartType;
use JayI\Polycart\Domains\Sharing\Actions\ShareCartAction;
use JayI\Polycart\Domains\Sharing\Actions\UnshareCartAction;
use JayI\Polycart\Domains\Sharing\Models\CartMemberModel;

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
    public function create(string $type, Model|string|null $owner = null, array $attributes = [], ?Model $scope = null): CartModel
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
    public function active(string $type, Model|string $owner, ?Model $scope = null): CartModel
    {
        return $this->activator->execute($type, $owner, $scope);
    }

    /**
     * Mark everything inside the callback as coming from a surface of your
     * own, such as `import` or `pos`: carts created inside it record it as
     * their `source`, and the audit log records it as the entries' surface.
     * A shortcut for Foundation's `Surface::using()`.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function usingSource(BackedEnum|string $source, Closure $callback): mixed
    {
        return app(Surface::class)->using($source instanceof BackedEnum ? (string) $source->value : $source, $callback);
    }

    /**
     * Share a cart with someone, or a whole scope such as a team, inside the
     * cart's boundary.
     */
    public function share(CartModel $cart, Model $member, string $role): CartMemberModel
    {
        return app(ShareCartAction::class)->execute($cart, $member, $role);
    }

    public function unshare(CartModel $cart, Model $member): void
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
    public function find(string $id): ?CartModel
    {
        return CartModel::query()->find($id);
    }

    /**
     * Turn a cart into another type, copying it unless `copy` is false.
     */
    public function convert(CartModel $cart, string $to, bool $copy = true): CartModel
    {
        return $this->converter->execute($cart, $to, $copy);
    }

    /**
     * Move every line of one cart into another and delete the first.
     */
    public function merge(CartModel $from, CartModel $into): CartModel
    {
        return $this->merger->execute($from, $into);
    }
}
