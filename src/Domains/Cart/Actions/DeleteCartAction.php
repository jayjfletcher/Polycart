<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Cart\Actions;

use RefactorCircus\Polycart\Domains\Cart\Events\CartDeletedActionEvent;
use RefactorCircus\Polycart\Domains\Cart\Events\CartDeletingActionEvent;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;

/**
 * Soft-delete a cart. Its lines stay with it until it is pruned.
 */
final class DeleteCartAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(CartModel $cart): void
    {
        CartDeletingActionEvent::dispatch($cart);

        $this->perform($cart);

        CartDeletedActionEvent::dispatch($cart);
    }

    private function perform(CartModel $cart): void
    {
        $cart->delete();
    }
}
