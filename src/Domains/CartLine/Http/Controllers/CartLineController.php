<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\CartLine\Http\Requests\DestroyLinesRequest;
use RefactorCircus\Polycart\Domains\CartLine\Http\Requests\StoreLinesRequest;
use RefactorCircus\Polycart\Domains\CartLine\Http\Requests\UpdateLinesRequest;

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
