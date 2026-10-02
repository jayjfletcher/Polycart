<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Ui;

use Illuminate\Contracts\View\View;
use JayI\Polycart\Actions\ListCartTypesAction;
use JayI\Polycart\Http\Ui\Concerns\AuthorizesScreens;
use JayI\Polycart\Models\Cart;

/**
 * The cart types page, behind the JSON API's own check: `viewAny` on Cart.
 */
final class CartTypeUiController
{
    use AuthorizesScreens;

    public function __invoke(): View
    {
        $this->authorizeScreen('viewAny', Cart::class);

        /** @var view-string $view */
        $view = 'polycart::ui.types.index';

        return view($view, [
            'types' => app(ListCartTypesAction::class)->execute(),
        ]);
    }
}
