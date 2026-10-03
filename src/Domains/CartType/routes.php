<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use JayI\Polycart\Domains\CartType\Http\Controllers\CartTypeController;

Route::get('types', [CartTypeController::class, 'index'])->name('types.index');
