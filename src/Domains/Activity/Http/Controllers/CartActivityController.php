<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Activity\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Domains\Activity\Http\Requests\IndexActivityRequest;
use JayI\Polycart\Domains\Cart\Models\CartModel;

final class CartActivityController
{
    public function index(IndexActivityRequest $request, CartModel $cart): JsonResponse
    {
        return $request->persist();
    }
}
