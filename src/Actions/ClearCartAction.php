<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use JayI\Polycart\Enums\Activity;
use JayI\Polycart\Events\Action\CartClearedActionEvent;
use JayI\Polycart\Events\Action\CartClearingActionEvent;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Support\ActivityRecorder;

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

    public function execute(Cart $cart): Cart
    {
        CartClearingActionEvent::dispatch($cart);

        $result = $this->perform($cart);

        CartClearedActionEvent::dispatch($result);

        return $result;
    }

    private function perform(Cart $cart): Cart
    {
        foreach ($cart->lines()->get() as $line) {
            $this->remove->execute($line);
        }

        app(ActivityRecorder::class)->record($cart, Activity::Cleared);

        return $cart->unsetRelation('lines');
    }
}
