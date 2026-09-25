<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use JayI\Polycart\Enums\CartSource as CartSourceEnum;
use JayI\Polycart\Http\Controllers\CartController;
use JayI\Polycart\Http\Controllers\CartLineController;
use JayI\Polycart\Http\Controllers\CartMemberController;
use JayI\Polycart\Http\Controllers\CartTypeController;
use JayI\Polycart\Http\Middleware\CartSource;

/** @var string $prefix */
$prefix = config('polycart.routes.prefix');

/** @var array<int, string> $middleware */
$middleware = config('polycart.routes.middleware');

Route::prefix($prefix)->middleware([...$middleware, CartSource::class.':'.CartSourceEnum::Api->value])->name('polycart.')->group(function (): void {
    Route::get('types', [CartTypeController::class, 'index'])->name('types.index');

    Route::get('carts', [CartController::class, 'index'])->name('carts.index');
    Route::post('carts', [CartController::class, 'store'])->name('carts.store');
    Route::post('carts/active', [CartController::class, 'active'])->name('carts.active');
    Route::get('carts/{cart}', [CartController::class, 'show'])->name('carts.show');
    Route::get('carts/{cart}/activity', [CartController::class, 'activity'])->name('carts.activity');
    Route::patch('carts/{cart}', [CartController::class, 'update'])->name('carts.update');
    Route::delete('carts/{cart}', [CartController::class, 'destroy'])->name('carts.destroy');
    Route::post('carts/{cart}/clear', [CartController::class, 'clear'])->name('carts.clear');
    Route::post('carts/{cart}/status', [CartController::class, 'transition'])->name('carts.transition');
    Route::post('carts/{cart}/convert', [CartController::class, 'convert'])->name('carts.convert');
    Route::post('carts/{cart}/merge', [CartController::class, 'merge'])->name('carts.merge');
    Route::put('carts/{cart}/visibility', [CartMemberController::class, 'visibility'])->name('carts.visibility');

    // Lines change in batches, all or nothing. Each line is looked up within
    // the cart, so a batch can never reach another cart's lines.
    Route::post('carts/{cart}/lines', [CartLineController::class, 'store'])->name('carts.lines.store');
    Route::patch('carts/{cart}/lines', [CartLineController::class, 'update'])->name('carts.lines.update');
    Route::delete('carts/{cart}/lines', [CartLineController::class, 'destroy'])->name('carts.lines.destroy');

    // Scoped so a member can only be reached through its own cart.
    Route::scopeBindings()->group(function (): void {
        Route::get('carts/{cart}/members', [CartMemberController::class, 'index'])->name('carts.members.index');
        Route::post('carts/{cart}/members', [CartMemberController::class, 'store'])->name('carts.members.store');
        Route::delete('carts/{cart}/members/{member}', [CartMemberController::class, 'destroy'])->name('carts.members.destroy');
    });
});
