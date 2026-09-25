<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use JayI\Polycart\Enums\Activity;
use JayI\Polycart\Events\Action\LineRemovedActionEvent;
use JayI\Polycart\Events\Action\LineRemovingActionEvent;
use JayI\Polycart\Models\CartLine;
use JayI\Polycart\Support\ActivityRecorder;

final class RemoveLineAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(CartLine $line): void
    {
        $cart = $line->cart;
        $lineId = $line->id;

        LineRemovingActionEvent::dispatch($cart, $line);

        $this->perform($line);

        LineRemovedActionEvent::dispatch($cart, $lineId);
    }

    private function perform(CartLine $line): void
    {
        $cart = $line->cart;

        $line->delete();

        $cart->extendLifetime();
        $cart->unsetRelation('lines');

        app(ActivityRecorder::class)->record($cart, Activity::LineRemoved, ['line' => $line->id]);
    }
}
