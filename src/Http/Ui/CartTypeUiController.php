<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Ui;

use Illuminate\Contracts\View\View;
use JayI\Polycart\Actions\ListCartTypesAction;

final class CartTypeUiController
{
    public function __invoke(): View
    {
        /** @var view-string $view */
        $view = 'polycart::ui.types.index';

        return view($view, [
            'types' => app(ListCartTypesAction::class)->execute(),
        ]);
    }
}
