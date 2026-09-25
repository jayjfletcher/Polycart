<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Http\Requests\DestroyLinesRequest;
use JayI\Polycart\Http\Requests\StoreLinesRequest;
use JayI\Polycart\Http\Requests\UpdateLinesRequest;
use JayI\Polycart\Models\Cart;

final class CartLineController
{
    public function store(StoreLinesRequest $request, Cart $cart): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateLinesRequest $request, Cart $cart): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DestroyLinesRequest $request, Cart $cart): JsonResponse
    {
        return $request->persist();
    }
}
