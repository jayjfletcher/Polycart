<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use Illuminate\Validation\Rule;
use JayI\Polycart\Enums\Activity;
use JayI\Polycart\Enums\Visibility;
use JayI\Polycart\Events\Action\VisibilityChangedActionEvent;
use JayI\Polycart\Events\Action\VisibilityChangingActionEvent;
use JayI\Polycart\Exceptions\SharingException;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Support\ActivityRecorder;

/**
 * Widen or narrow who sees a cart without being shared in.
 */
final class SetVisibilityAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'visibility' => ['required', Rule::enum(Visibility::class)],
        ];
    }

    public function execute(Cart $cart, Visibility|string $visibility): Cart
    {
        $from = $cart->visibility ?? Visibility::Private;

        VisibilityChangingActionEvent::dispatch($cart, $visibility instanceof Visibility ? $visibility : Visibility::from($visibility));

        $result = $this->perform($cart, $visibility);

        VisibilityChangedActionEvent::dispatch($result, $from, $result->visibility ?? Visibility::Private);

        return $result;
    }

    private function perform(Cart $cart, Visibility|string $visibility): Cart
    {
        $to = $visibility instanceof Visibility ? $visibility : Visibility::from($visibility);
        $from = $cart->visibility ?? Visibility::Private;

        if ($to !== Visibility::Private) {
            if (! $cart->cartType()->shareable()) {
                throw SharingException::notShareable($cart->type);
            }

            if (! $cart->isScoped()) {
                throw SharingException::unscoped($cart->id);
            }
        }

        if ($from === $to) {
            return $cart;
        }

        $cart->visibility = $to;
        $cart->save();

        app(ActivityRecorder::class)->record($cart, Activity::VisibilityChanged, ['from' => $from->value, 'to' => $to->value]);

        return $cart;
    }
}
