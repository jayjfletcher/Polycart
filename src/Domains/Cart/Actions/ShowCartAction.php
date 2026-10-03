<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Actions;

use JayI\Polycart\Domains\Cart\Events\CartShowingActionEvent;
use JayI\Polycart\Domains\Cart\Events\CartShownActionEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;

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
    public function execute(CartModel $cart): CartModel
    {
        CartShowingActionEvent::dispatch($cart);

        $result = $this->perform($cart);

        CartShownActionEvent::dispatch($result);

        return $result;
    }

    /**
     * The cart with its lines, members, and the size of the tree beneath it.
     */
    private function perform(CartModel $cart): CartModel
    {
        return $cart->load(['lines', 'members'])->loadCount('children');
    }
}
