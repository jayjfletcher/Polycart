<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use JayI\Polycart\Enums\Activity;
use JayI\Polycart\Events\Action\LineUpdatedActionEvent;
use JayI\Polycart\Events\Action\LineUpdatingActionEvent;
use JayI\Polycart\Models\CartLine;
use JayI\Polycart\Support\ActivityRecorder;

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

    public function execute(CartLine $line, int $quantity, ?int $unitPrice = null): ?CartLine
    {
        $cart = $line->cart;

        LineUpdatingActionEvent::dispatch($cart, $line, $quantity, $unitPrice);

        $result = $this->perform($line, $quantity, $unitPrice);

        LineUpdatedActionEvent::dispatch($cart, $result);

        return $result;
    }

    private function perform(CartLine $line, int $quantity, ?int $unitPrice = null): ?CartLine
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

        app(ActivityRecorder::class)->record($cart, Activity::LineUpdated, ['line' => $line->id, 'quantity' => $line->quantity]);

        return $line;
    }
}
