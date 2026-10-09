<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Actions;

use BackedEnum;
use RefactorCircus\Polycart\Domains\Cart\Events\CartTransitionedActionEvent;
use RefactorCircus\Polycart\Domains\Cart\Events\CartTransitioningActionEvent;
use RefactorCircus\Polycart\Domains\Cart\Exceptions\InvalidTransitionException;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;

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

    public function execute(CartModel $cart, BackedEnum|string $status): CartModel
    {
        $from = $cart->status;

        CartTransitioningActionEvent::dispatch($cart, (string) CartModel::statusValue($status));

        $result = $this->perform($cart, $status);

        CartTransitionedActionEvent::dispatch($result, $from, (string) $result->status);

        return $result;
    }

    private function perform(CartModel $cart, BackedEnum|string $status): CartModel
    {
        $type = $cart->cartType();
        $statuses = $type->statuses();

        $to = $status instanceof BackedEnum
            ? $status
            : ($statuses === null ? null : $statuses::tryFrom($status));
        $value = (string) CartModel::statusValue($status);

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

        return $cart;
    }
}
