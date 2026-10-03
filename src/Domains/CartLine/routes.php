<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use JayI\Polycart\Domains\CartLine\Http\Controllers\CartLineController;

// Lines change in batches, all or nothing. Each line is looked up within the
// cart, so a batch can never reach another cart's lines.
Route::post('carts/{cart}/lines', [CartLineController::class, 'store'])->name('carts.lines.store');
Route::patch('carts/{cart}/lines', [CartLineController::class, 'update'])->name('carts.lines.update');
Route::delete('carts/{cart}/lines', [CartLineController::class, 'destroy'])->name('carts.lines.destroy');
