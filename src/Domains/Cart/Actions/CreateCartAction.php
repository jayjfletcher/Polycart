<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use RefactorCircus\Polycart\Domains\Cart\Events\CartCreatedActionEvent;
use RefactorCircus\Polycart\Domains\Cart\Events\CartCreatingActionEvent;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\CartType\Services\CartTypeRegistry;
use RefactorCircus\Polycart\Domains\Scope\Contracts\CartParticipant;
use RefactorCircus\Polycart\Domains\Scope\Contracts\CartScope;
use RefactorCircus\Polycart\Domains\Scope\Exceptions\InvalidScopeException;
use RefactorCircus\Polycart\Domains\Scope\Services\ScopeTree;

/**
 * Start a new cart of a type, hydrated as that type's model.
 */
final class CreateCartAction
{
    public function __construct(
        private readonly CartTypeRegistry $types,
        private readonly ScopeTree $tree,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:191'],
            'owner_type' => ['nullable', 'string', 'max:191', 'required_with:owner_id', 'prohibits:session_key'],
            'owner_id' => ['nullable', 'string', 'max:191', 'required_with:owner_type'],
            'session_key' => ['sometimes', 'nullable', 'string', 'max:191'],
            'label' => ['sometimes', 'nullable', 'string', 'max:191'],
            'meta' => ['sometimes', 'nullable', 'array'],
            'parent_id' => ['sometimes', 'nullable', 'string', 'max:26'],
            'scope_type' => ['nullable', 'string', 'max:191', 'required_with:scope_id'],
            'scope_id' => ['nullable', 'string', 'max:191', 'required_with:scope_type'],
        ];
    }

    /**
     * @param  Model|string|null  $owner  A model, or a guest's session key.
     * @param  array<string, mixed>  $attributes  Any of label, meta, parent_id.
     * @param  Model|null  $scope  The level of the tree the cart lives in, such as a team.
     */
    public function execute(string $type, Model|string|null $owner = null, array $attributes = [], ?Model $scope = null): CartModel
    {
        CartCreatingActionEvent::dispatch($type, $owner, $attributes, $scope);

        $result = $this->perform($type, $owner, $attributes, $scope);

        CartCreatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  Model|string|null  $owner  A model, or a guest's session key.
     * @param  array<string, mixed>  $attributes  Any of label, meta, parent_id.
     * @param  Model|null  $scope  The level of the tree the cart lives in, such as a team.
     */
    private function perform(string $type, Model|string|null $owner = null, array $attributes = [], ?Model $scope = null): CartModel
    {
        $class = $this->types->get($type)->model();

        /** @var CartModel $cart */
        $cart = new $class;

        $cart->fill(Arr::only($attributes, ['label', 'meta', 'parent_id']));
        $cart->forceFill([...self::owner($owner), 'type' => $type]);

        if ($scope !== null) {
            if (! $scope instanceof CartScope) {
                throw InvalidScopeException::notAScope($scope::class);
            }

            // Someone can only start a cart in a part of the tree they are in.
            if ($owner instanceof CartParticipant && ! $this->tree->reaches($owner, $scope->getMorphClass(), (string) $scope->getKey())) {
                throw InvalidScopeException::outside($scope->getMorphClass().':'.$scope->getKey());
            }

            $cart->placeIn($scope);
        }

        $cart->save();

        return $cart;
    }

    /**
     * @return array<string, string|null>
     */
    private static function owner(Model|string|null $owner): array
    {
        if ($owner instanceof Model) {
            return [
                'owner_type' => $owner->getMorphClass(),
                'owner_id' => (string) $owner->getKey(),
            ];
        }

        return $owner === null ? [] : ['session_key' => $owner];
    }
}
