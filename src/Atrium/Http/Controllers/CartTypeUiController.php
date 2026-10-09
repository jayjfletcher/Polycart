<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Atrium\Http\Controllers;

use Illuminate\Contracts\View\View;
use RefactorCircus\Polycart\Atrium\Http\Controllers\Concerns\AuthorizesScreens;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\CartType\Actions\ListCartTypesAction;

/**
 * The cart types page, behind the JSON API's own check: `viewAny` on CartModel.
 */
final class CartTypeUiController
{
    use AuthorizesScreens;

    public function __invoke(): View
    {
        $this->authorizeScreen('viewAny', CartModel::class);

        /** @var view-string $view */
        $view = 'polycart::ui.types.index';

        return view($view, [
            'types' => app(ListCartTypesAction::class)->execute(),
        ]);
    }
}
