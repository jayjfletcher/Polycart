<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use RefactorCircus\Polycart\Domains\Sharing\Http\Controllers\CartMemberController;

Route::put('carts/{cart}/visibility', [CartMemberController::class, 'visibility'])->name('carts.visibility');

// Scoped so a member can only be reached through its own cart.
Route::scopeBindings()->group(function (): void {
    Route::get('carts/{cart}/members', [CartMemberController::class, 'index'])->name('carts.members.index');
    Route::post('carts/{cart}/members', [CartMemberController::class, 'store'])->name('carts.members.store');
    Route::delete('carts/{cart}/members/{member}', [CartMemberController::class, 'destroy'])->name('carts.members.destroy');
});
