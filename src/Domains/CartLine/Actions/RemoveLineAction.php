<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Actions;

use JayI\Polycart\Domains\CartLine\Events\LineRemovedActionEvent;
use JayI\Polycart\Domains\CartLine\Events\LineRemovingActionEvent;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;

final class RemoveLineAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(CartLineModel $line): void
    {
        $cart = $line->cart;
        $lineId = $line->id;

        LineRemovingActionEvent::dispatch($cart, $line);

        $this->perform($line);

        LineRemovedActionEvent::dispatch($cart, $lineId);
    }

    private function perform(CartLineModel $line): void
    {
        $cart = $line->cart;

        $line->delete();

        $cart->extendLifetime();
        $cart->unsetRelation('lines');
    }
}
