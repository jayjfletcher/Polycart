<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Actions;

use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;
use JayI\Foundation\Support\Surface;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartLine\Events\LineAddedActionEvent;
use JayI\Polycart\Domains\CartLine\Events\LineAddingActionEvent;
use JayI\Polycart\Domains\CartLine\Exceptions\LineRejectedException;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;
use JayI\Polycart\Domains\CartLine\Support\PendingLine;

/**
 * Put something in a cart by sending it through the cart type's stages.
 *
 * The whole pipeline runs in one transaction: a stage that rejects the line
 * leaves the cart exactly as it was.
 */
final class AddLineAction
{
    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly Pipeline $pipeline,
        private readonly Auth $auth,
        private readonly Surface $surface,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'purchasable_type' => ['nullable', 'string', 'max:191', 'required_with:purchasable_id'],
            'purchasable_id' => ['nullable', 'string', 'max:191', 'required_with:purchasable_type'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'options' => ['sometimes', 'array'],
            'meta' => ['sometimes', 'array'],
            'unit_price' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @param  array<string, mixed>  $meta
     */
    public function execute(
        CartModel $cart,
        ?Model $purchasable,
        int $quantity = 1,
        array $options = [],
        array $meta = [],
        ?int $unitPrice = null,
    ): CartLineModel {
        LineAddingActionEvent::dispatch($cart, $purchasable, $quantity, $options, $meta, $unitPrice);

        $result = $this->perform($cart, $purchasable, $quantity, $options, $meta, $unitPrice);

        LineAddedActionEvent::dispatch($cart, $result, ! $result->wasRecentlyCreated);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $options
     * @param  array<string, mixed>  $meta
     */
    private function perform(
        CartModel $cart,
        ?Model $purchasable,
        int $quantity = 1,
        array $options = [],
        array $meta = [],
        ?int $unitPrice = null,
    ): CartLineModel {
        $type = $cart->cartType();
        $user = $this->auth->guard()->user();

        $pending = new PendingLine(
            cart: $cart,
            type: $type,
            purchasable: $purchasable,
            quantity: $quantity,
            options: $options,
            meta: $meta,
            unitPrice: $unitPrice,
            actor: $user instanceof Model ? $user : null,
            source: $this->surface->current(),
        );

        $pending = $this->db->transaction(fn (): PendingLine => $this->pipeline
            ->send($pending)
            ->through($type->addLineStages())
            ->thenReturn());

        // A stage that returns without passing the line on, and without
        // rejecting it, would otherwise look like a silent success.
        if (! $pending->line instanceof CartLineModel || ! $pending->line->exists) {
            throw LineRejectedException::stopped();
        }

        $line = $pending->line;

        $cart->extendLifetime();
        $cart->unsetRelation('lines');

        return $line;
    }
}
