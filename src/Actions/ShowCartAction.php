<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use JayI\Polycart\Events\Action\CartShowingActionEvent;
use JayI\Polycart\Events\Action\CartShownActionEvent;
use JayI\Polycart\Models\Cart;

final class ShowCartAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    /**
     * The cart with its lines, members, and the size of the tree beneath it.
     */
    public function execute(Cart $cart): Cart
    {
        CartShowingActionEvent::dispatch($cart);

        $result = $this->perform($cart);

        CartShownActionEvent::dispatch($result);

        return $result;
    }

    /**
     * The cart with its lines, members, and the size of the tree beneath it.
     */
    private function perform(Cart $cart): Cart
    {
        return $cart->load(['lines', 'members'])->loadCount('children');
    }
}
