<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use Illuminate\Support\Arr;
use JayI\Polycart\Enums\Activity;
use JayI\Polycart\Events\Action\CartUpdatedActionEvent;
use JayI\Polycart\Events\Action\CartUpdatingActionEvent;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Support\ActivityRecorder;

/**
 * Change a cart's label or meta.
 *
 * Type, status, and owner are not editable here: type and status change
 * through conversion and transition, which enforce the type's rules.
 */
final class UpdateCartAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'label' => ['sometimes', 'nullable', 'string', 'max:191'],
            'meta' => ['sometimes', 'nullable', 'array'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Cart $cart, array $data): Cart
    {
        CartUpdatingActionEvent::dispatch($cart, $data);

        $result = $this->perform($cart, $data);

        CartUpdatedActionEvent::dispatch($result, array_keys(Arr::only($result->getChanges(), ['label', 'meta'])));

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(Cart $cart, array $data): Cart
    {
        $cart->fill(Arr::only($data, ['label', 'meta']));

        $changed = array_keys($cart->getDirty());

        $cart->save();

        if ($changed !== []) {
            app(ActivityRecorder::class)->record($cart, Activity::Updated, ['changed' => $changed]);
        }

        return $cart;
    }
}
