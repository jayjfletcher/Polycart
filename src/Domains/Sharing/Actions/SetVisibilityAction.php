<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Sharing\Actions;

use Illuminate\Validation\Rule;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\Sharing\Enums\Visibility;
use RefactorCircus\Polycart\Domains\Sharing\Events\VisibilityChangedActionEvent;
use RefactorCircus\Polycart\Domains\Sharing\Events\VisibilityChangingActionEvent;
use RefactorCircus\Polycart\Domains\Sharing\Exceptions\SharingException;

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

    public function execute(CartModel $cart, Visibility|string $visibility): CartModel
    {
        $from = $cart->visibility ?? Visibility::Private;

        VisibilityChangingActionEvent::dispatch($cart, $visibility instanceof Visibility ? $visibility : Visibility::from($visibility));

        $result = $this->perform($cart, $visibility);

        VisibilityChangedActionEvent::dispatch($result, $from, $result->visibility ?? Visibility::Private);

        return $result;
    }

    private function perform(CartModel $cart, Visibility|string $visibility): CartModel
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

        return $cart;
    }
}
