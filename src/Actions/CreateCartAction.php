<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use JayI\Polycart\Access\ScopeTree;
use JayI\Polycart\Contracts\CartParticipant;
use JayI\Polycart\Contracts\CartScope;
use JayI\Polycart\Events\Action\CartCreatedActionEvent;
use JayI\Polycart\Events\Action\CartCreatingActionEvent;
use JayI\Polycart\Exceptions\InvalidScopeException;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Types\CartTypeRegistry;

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
    public function execute(string $type, Model|string|null $owner = null, array $attributes = [], ?Model $scope = null): Cart
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
    private function perform(string $type, Model|string|null $owner = null, array $attributes = [], ?Model $scope = null): Cart
    {
        $class = $this->types->get($type)->model();

        /** @var Cart $cart */
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
