<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;
use JayI\Polycart\Enums\Activity;
use JayI\Polycart\Events\Action\LineAddedActionEvent;
use JayI\Polycart\Events\Action\LineAddingActionEvent;
use JayI\Polycart\Exceptions\LineRejectedException;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartLine;
use JayI\Polycart\Pipeline\PendingLine;
use JayI\Polycart\Support\ActivityRecorder;
use JayI\Polycart\Support\SourceContext;

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
        private readonly SourceContext $sources,
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
        Cart $cart,
        ?Model $purchasable,
        int $quantity = 1,
        array $options = [],
        array $meta = [],
        ?int $unitPrice = null,
    ): CartLine {
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
        Cart $cart,
        ?Model $purchasable,
        int $quantity = 1,
        array $options = [],
        array $meta = [],
        ?int $unitPrice = null,
    ): CartLine {
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
            source: $this->sources->current(),
        );

        $pending = $this->db->transaction(fn (): PendingLine => $this->pipeline
            ->send($pending)
            ->through($type->addLineStages())
            ->thenReturn());

        // A stage that returns without passing the line on, and without
        // rejecting it, would otherwise look like a silent success.
        if (! $pending->line instanceof CartLine || ! $pending->line->exists) {
            throw LineRejectedException::stopped();
        }

        $line = $pending->line;
        $merged = $pending->merging();

        $cart->extendLifetime();
        $cart->unsetRelation('lines');

        app(ActivityRecorder::class)->record($cart, $merged ? Activity::LineUpdated : Activity::LineAdded, [
            'line' => $line->id,
            'quantity' => $line->quantity,
        ]);

        return $line;
    }
}
