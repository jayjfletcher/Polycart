<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Actions;

use RefactorCircus\Polycart\Domains\Cart\Events\CartClearedActionEvent;
use RefactorCircus\Polycart\Domains\Cart\Events\CartClearingActionEvent;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\CartLine\Actions\RemoveLineAction;

/**
 * Remove every line from a cart.
 */
final class ClearCartAction
{
    public function __construct(private readonly RemoveLineAction $remove) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(CartModel $cart): CartModel
    {
        CartClearingActionEvent::dispatch($cart);

        $result = $this->perform($cart);

        CartClearedActionEvent::dispatch($result);

        return $result;
    }

    private function perform(CartModel $cart): CartModel
    {
        foreach ($cart->lines()->get() as $line) {
            $this->remove->execute($line);
        }

        return $cart->unsetRelation('lines');
    }
}
