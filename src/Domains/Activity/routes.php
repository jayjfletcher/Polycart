<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use JayI\Polycart\Domains\Activity\Http\Controllers\CartActivityController;

Route::get('carts/{cart}/activity', [CartActivityController::class, 'index'])->name('carts.activity');
