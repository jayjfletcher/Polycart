<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use JayI\Polycart\Enums\Activity;
use JayI\Polycart\Events\Action\CartDeletedActionEvent;
use JayI\Polycart\Events\Action\CartDeletingActionEvent;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Support\ActivityRecorder;

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

    public function execute(Cart $cart): void
    {
        CartDeletingActionEvent::dispatch($cart);

        $this->perform($cart);

        CartDeletedActionEvent::dispatch($cart);
    }

    private function perform(Cart $cart): void
    {
        app(ActivityRecorder::class)->record($cart, Activity::Deleted);

        $cart->delete();
    }
}
