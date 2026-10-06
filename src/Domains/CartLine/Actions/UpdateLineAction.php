<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Actions;

use JayI\Polycart\Domains\CartLine\Events\LineUpdatedActionEvent;
use JayI\Polycart\Domains\CartLine\Events\LineUpdatingActionEvent;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;

/**
 * Change a line's quantity, removing it when the quantity reaches zero.
 */
final class UpdateLineAction
{
    public function __construct(private readonly RemoveLineAction $remove) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:0'],
            'unit_price' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }

    public function execute(CartLineModel $line, int $quantity, ?int $unitPrice = null): ?CartLineModel
    {
        $cart = $line->cart;

        LineUpdatingActionEvent::dispatch($cart, $line, $quantity, $unitPrice);

        $result = $this->perform($line, $quantity, $unitPrice);

        LineUpdatedActionEvent::dispatch($cart, $result);

        return $result;
    }

    private function perform(CartLineModel $line, int $quantity, ?int $unitPrice = null): ?CartLineModel
    {
        if ($quantity < 1) {
            $this->remove->execute($line);

            return null;
        }

        $cart = $line->cart;

        $line->quantity = $quantity;
        $line->unit_price = $unitPrice ?? $line->unit_price;

        $cart->cartType()->validate($cart, $line);
        $line->save();

        $cart->extendLifetime();
        $cart->unsetRelation('lines');

        return $line;
    }
}
