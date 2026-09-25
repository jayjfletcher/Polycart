<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use JayI\Polycart\Enums\Activity;
use JayI\Polycart\Events\Action\CartConvertedActionEvent;
use JayI\Polycart\Events\Action\CartConvertingActionEvent;
use JayI\Polycart\Exceptions\InvalidConversionException;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Support\ActivityRecorder;
use JayI\Polycart\Support\SourceContext;
use JayI\Polycart\Types\CartType;
use JayI\Polycart\Types\CartTypeRegistry;

/**
 * Turn a cart into another type: a cart into a quote, a quote into an order.
 *
 * The converted cart records the source of the conversion, not the original's,
 * whether it is a copy or the same cart retyped.
 *
 * The source type must list the target in convertsTo(). Every line is checked
 * against the target type, so a type that requires prices refuses a cart with
 * unpriced lines and nothing is written.
 */
final class ConvertCartAction
{
    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly CartTypeRegistry $types,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'to' => ['required', 'string', 'max:191'],
            'copy' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @param  bool  $copy  Copy the cart and its lines (true) or retype it in place (false).
     */
    public function execute(Cart $cart, string $to, bool $copy = true): Cart
    {
        $from = $cart->type;

        CartConvertingActionEvent::dispatch($cart, $to, $copy);

        $result = $this->perform($cart, $to, $copy);

        CartConvertedActionEvent::dispatch($cart, $result, $from, $to, $copy);

        return $result;
    }

    /**
     * @param  bool  $copy  Copy the cart and its lines (true) or retype it in place (false).
     */
    private function perform(Cart $cart, string $to, bool $copy = true): Cart
    {
        $source = $cart->cartType();

        if (! in_array($to, $source->convertsTo(), true)) {
            throw InvalidConversionException::notAllowed($source->key(), $to);
        }

        $target = $this->types->get($to);

        $converted = $this->db->transaction(function () use ($cart, $target, $source, $copy): Cart {
            $converted = $copy ? $this->copy($cart, $target) : $this->retype($cart, $target);
            $recorder = app(ActivityRecorder::class);

            if ($copy) {
                // The copy carries the original's history, so every source
                // that touched what became this cart is on record.
                $recorder->record($cart, Activity::Converted, ['from' => $source->key(), 'to' => $target->key(), 'into' => $converted->id]);
                $recorder->inherit($cart, $converted);
                $recorder->record($converted, Activity::ConvertedFrom, ['from' => $source->key(), 'to' => $target->key(), 'cart' => $cart->id]);
            } else {
                $recorder->record($converted, Activity::Converted, ['from' => $source->key(), 'to' => $target->key()]);
            }

            return $converted;
        });

        return $converted;
    }

    private function copy(Cart $cart, CartType $target): Cart
    {
        $class = $target->model();

        /** @var Cart $converted */
        $converted = new $class;

        $converted->forceFill([
            'type' => $target->key(),
            'owner_type' => $cart->owner_type,
            'owner_id' => $cart->owner_id,
            'session_key' => $cart->session_key,
            'label' => $cart->label,
            'meta' => $cart->meta,
        ]);

        // The copy stays in the same tree, and everyone keeps their access.
        $converted->inheritPlaceFrom($cart);
        $converted->save();

        foreach ($cart->members()->get() as $member) {
            $converted->members()->firstOrCreate(
                ['member_type' => $member->member_type, 'member_id' => $member->member_id],
                ['role' => $this->roleIn($target, $member->role)],
            );
        }

        foreach ($cart->lines()->get() as $line) {
            $copy = $line->replicate(['cart_id']);
            $copy->cart()->associate($converted);

            $target->validate($converted, $copy);
            $copy->save();
        }

        return $this->finish($converted, $cart, $target);
    }

    private function retype(Cart $cart, CartType $target): Cart
    {
        $lifetime = $target->lifetime();

        $cart->forceFill([
            'type' => $target->key(),
            // A cart that became an order through MCP is an MCP order.
            'source' => app(SourceContext::class)->current(),
            'status' => Cart::statusValue($target->initialStatus()),
            'expires_at' => $lifetime === null ? null : Carbon::now()->add($lifetime),
        ])->save();

        // A role the target type does not have would silently grant nothing,
        // so it becomes the target's weakest role, as it does on a copy.
        foreach ($cart->members()->get() as $member) {
            $member->update(['role' => $this->roleIn($target, $member->role)]);
        }

        // Re-read so the cart hydrates as the target type's model.
        $converted = Cart::query()->withoutGlobalScopes()->findOrFail($cart->id);

        foreach ($converted->lines as $line) {
            $target->validate($converted, $line);
        }

        return $this->finish($converted, $cart, $target);
    }

    /**
     * A member keeps their role when the target type has it, and otherwise
     * gets the target's weakest role rather than more access than before.
     */
    private function roleIn(CartType $target, string $role): string
    {
        return array_key_exists($role, $target->roles()) ? $role : $target->visibilityRole();
    }

    private function finish(Cart $converted, Cart $source, CartType $target): Cart
    {
        $target->convertedFrom($converted, $source);

        $converted->save();
        $converted->unsetRelation('lines');

        return $converted;
    }
}
