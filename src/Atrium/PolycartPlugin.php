<?php

declare(strict_types=1);

namespace JayI\Polycart\Atrium;

use Atrium\Atrium\Navigation\NavItem;
use Atrium\Atrium\Plugins\Plugin;
use Atrium\Atrium\Search\SearchResult;
use Atrium\Atrium\Search\SearchSource;
use Atrium\Atrium\Widgets\WidgetDefinition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use JayI\Polycart\Enums\CartSource;
use JayI\Polycart\Http\Middleware\CartSource as CartSourceMiddleware;
use JayI\Polycart\Http\Ui\CartTypeUiController;
use JayI\Polycart\Http\Ui\CartUiController;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Types\CartTypeRegistry;

/**
 * Registers Polycart inside the Atrium dashboard.
 *
 * Widgets declared here are offered in Atrium's picker. None is ever placed
 * on a dashboard automatically; that is always a user's choice.
 */
class PolycartPlugin extends Plugin
{
    public function key(): string
    {
        return 'polycart';
    }

    public function label(): string
    {
        return 'Polycart';
    }

    public function navigation(): array
    {
        return [
            NavItem::make(__('polycart::polycart.carts'))->route('atrium.polycart.carts.index')->group('Polycart')->sort(10),
            NavItem::make(__('polycart::polycart.types'))->route('atrium.polycart.types.index')->group('Polycart')->sort(20),
        ];
    }

    public function routes(): void
    {
        Route::name('polycart.')->middleware(CartSourceMiddleware::class.':'.CartSource::Atrium->value)->group(function (): void {
            Route::get('polycart/carts', [CartUiController::class, 'index'])->name('carts.index');
            Route::get('polycart/carts/{cart}', [CartUiController::class, 'show'])->name('carts.show');
            Route::patch('polycart/carts/{cart}', [CartUiController::class, 'update'])->name('carts.update');
            Route::delete('polycart/carts/{cart}', [CartUiController::class, 'destroy'])->name('carts.destroy');
            Route::post('polycart/carts/{cart}/clear', [CartUiController::class, 'clear'])->name('carts.clear');
            Route::post('polycart/carts/{cart}/status', [CartUiController::class, 'transition'])->name('carts.transition');
            Route::post('polycart/carts/{cart}/convert', [CartUiController::class, 'convert'])->name('carts.convert');

            Route::put('polycart/carts/{cart}/visibility', [CartUiController::class, 'visibility'])->name('carts.visibility');
            Route::post('polycart/carts/{cart}/members', [CartUiController::class, 'share'])->name('carts.members.store');

            Route::scopeBindings()->group(function (): void {
                Route::delete('polycart/carts/{cart}/members/{member}', [CartUiController::class, 'unshare'])->name('carts.members.destroy');
                Route::patch('polycart/carts/{cart}/lines/{line}', [CartUiController::class, 'updateLine'])->name('carts.lines.update');
                Route::delete('polycart/carts/{cart}/lines/{line}', [CartUiController::class, 'removeLine'])->name('carts.lines.destroy');
            });

            Route::get('polycart/types', CartTypeUiController::class)->name('types.index');
        });
    }

    /**
     * Widget types Polycart makes available.
     *
     * Returning a definition offers the widget in the picker; it does not
     * place it on anyone's dashboard.
     */
    public function widgets(): array
    {
        return [
            WidgetDefinition::make('polycart.carts-by-type')
                ->label(__('polycart::polycart.widget_carts_by_type'))
                ->description(__('polycart::polycart.widget_carts_by_type_description'))
                ->defaultSize(6, 2)
                ->view('polycart::ui.widgets.carts-by-type')
                ->resolve(fn (): array => [
                    'counts' => collect(array_keys(app(CartTypeRegistry::class)->all()))
                        ->mapWithKeys(fn (string $type): array => [
                            $type => Cart::query()->ofType($type)->unexpired()->count(),
                        ])
                        ->all(),
                ]),

            WidgetDefinition::make('polycart.recent-carts')
                ->label(__('polycart::polycart.widget_recent_carts'))
                ->description(__('polycart::polycart.widget_recent_carts_description'))
                ->defaultSize(6, 2)
                ->view('polycart::ui.widgets.recent-carts')
                ->resolve(fn (): array => [
                    'carts' => Cart::query()->withCount('lines')->latest('updated_at')->limit(5)->get(),
                ]),
        ];
    }

    public function search(): ?SearchSource
    {
        return SearchSource::make('polycart')
            ->label('Polycart')
            ->using(fn (string $query): array => Cart::query()
                ->where(fn (Builder $builder): Builder => $builder
                    ->where('label', 'like', '%'.$query.'%')
                    ->orWhere('id', 'like', $query.'%'))
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(fn (Cart $cart): SearchResult => SearchResult::make(
                    $cart->label ?? $cart->id,
                    route('atrium.polycart.carts.show', $cart),
                )->subtitle($cart->type.($cart->status === null ? '' : ' · '.$cart->status))->group(__('polycart::polycart.carts')))
                ->all());
    }
}
