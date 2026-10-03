<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Actions;

use Illuminate\Support\Arr;
use JayI\Polycart\Domains\Activity\Enums\Activity;
use JayI\Polycart\Domains\Activity\Services\ActivityRecorder;
use JayI\Polycart\Domains\Cart\Events\CartUpdatedActionEvent;
use JayI\Polycart\Domains\Cart\Events\CartUpdatingActionEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;

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
    public function execute(CartModel $cart, array $data): CartModel
    {
        CartUpdatingActionEvent::dispatch($cart, $data);

        $result = $this->perform($cart, $data);

        CartUpdatedActionEvent::dispatch($result, array_keys(Arr::only($result->getChanges(), ['label', 'meta'])));

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(CartModel $cart, array $data): CartModel
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
