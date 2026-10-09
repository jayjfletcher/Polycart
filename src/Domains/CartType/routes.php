<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use RefactorCircus\Polycart\Domains\CartType\Http\Controllers\CartTypeController;

Route::get('types', [CartTypeController::class, 'index'])->name('types.index');
