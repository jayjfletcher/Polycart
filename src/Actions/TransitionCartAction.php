<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use BackedEnum;
use JayI\Polycart\Enums\Activity;
use JayI\Polycart\Events\Action\CartTransitionedActionEvent;
use JayI\Polycart\Events\Action\CartTransitioningActionEvent;
use JayI\Polycart\Exceptions\InvalidTransitionException;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Support\ActivityRecorder;

/**
 * Move a cart to another status of its own type.
 */
final class TransitionCartAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'status' => ['required', 'string', 'max:191'],
        ];
    }

    public function execute(Cart $cart, BackedEnum|string $status): Cart
    {
        $from = $cart->status;

        CartTransitioningActionEvent::dispatch($cart, (string) Cart::statusValue($status));

        $result = $this->perform($cart, $status);

        CartTransitionedActionEvent::dispatch($result, $from, (string) $result->status);

        return $result;
    }

    private function perform(Cart $cart, BackedEnum|string $status): Cart
    {
        $type = $cart->cartType();
        $statuses = $type->statuses();

        $to = $status instanceof BackedEnum
            ? $status
            : ($statuses === null ? null : $statuses::tryFrom($status));
        $value = (string) Cart::statusValue($status);

        if ($statuses === null || ! $to instanceof $statuses) {
            throw InvalidTransitionException::unknownStatus($type->key(), $value);
        }

        if (! $type->canTransition($cart->currentStatus(), $to)) {
            throw InvalidTransitionException::notAllowed($type->key(), $cart->status, $value);
        }

        $from = $cart->status;

        if ($from === $value) {
            return $cart;
        }

        $cart->status = $value;
        $cart->save();

        app(ActivityRecorder::class)->record($cart, Activity::StatusChanged, ['from' => $from, 'to' => $value]);

        return $cart;
    }
}
