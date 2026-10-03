<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartLine\Http\Requests\DestroyLinesRequest;
use JayI\Polycart\Domains\CartLine\Http\Requests\StoreLinesRequest;
use JayI\Polycart\Domains\CartLine\Http\Requests\UpdateLinesRequest;

final class CartLineController
{
    public function store(StoreLinesRequest $request, CartModel $cart): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateLinesRequest $request, CartModel $cart): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DestroyLinesRequest $request, CartModel $cart): JsonResponse
    {
        return $request->persist();
    }
}
