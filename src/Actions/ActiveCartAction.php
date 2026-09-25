<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Events\Action\ActiveCartResolvedActionEvent;
use JayI\Polycart\Events\Action\ActiveCartResolvingActionEvent;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Types\CartTypeRegistry;

/**
 * An owner's current cart of a type in a scope, started if they have none.
 *
 * A cart is current while it has not expired and is still in its type's
 * initial status. Once it moves on — checked out, submitted — the next call
 * starts a fresh one.
 */
final class ActiveCartAction
{
    public function __construct(
        private readonly CartTypeRegistry $types,
        private readonly CreateCartAction $create,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:191'],
            'owner_type' => ['required_without:session_key', 'nullable', 'string', 'max:191', 'required_with:owner_id', 'prohibits:session_key'],
            'owner_id' => ['required_with:owner_type', 'nullable', 'string', 'max:191'],
            'session_key' => ['required_without:owner_type', 'nullable', 'string', 'max:191'],
            'scope_type' => ['nullable', 'string', 'max:191', 'required_with:scope_id'],
            'scope_id' => ['nullable', 'string', 'max:191', 'required_with:scope_type'],
        ];
    }

    /**
     * @param  Model|null  $scope  Each scope has its own active cart, so
     *                             switching teams switches carts.
     */
    public function execute(string $type, Model|string $owner, ?Model $scope = null): Cart
    {
        ActiveCartResolvingActionEvent::dispatch($type, $owner, $scope);

        $result = $this->perform($type, $owner, $scope);

        ActiveCartResolvedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  Model|null  $scope  Each scope has its own active cart, so
     *                             switching teams switches carts.
     */
    private function perform(string $type, Model|string $owner, ?Model $scope = null): Cart
    {
        $initial = Cart::statusValue($this->types->get($type)->initialStatus());

        $query = Cart::query()->ofType($type)->ownedBy($owner)->unexpired();

        $scope === null
            ? $query->whereNull('scope_type')
            : $query->where('scope_type', $scope->getMorphClass())->where('scope_id', (string) $scope->getKey());

        $initial === null
            ? $query->whereNull('status')
            : $query->where('status', $initial);

        return $query->latest()->first() ?? $this->create->execute($type, $owner, scope: $scope);
    }
}
