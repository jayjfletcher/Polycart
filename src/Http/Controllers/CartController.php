<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use JayI\Polycart\Http\Requests\ActiveCartRequest;
use JayI\Polycart\Http\Requests\ClearCartRequest;
use JayI\Polycart\Http\Requests\ConvertCartRequest;
use JayI\Polycart\Http\Requests\DestroyCartRequest;
use JayI\Polycart\Http\Requests\IndexActivityRequest;
use JayI\Polycart\Http\Requests\IndexCartsRequest;
use JayI\Polycart\Http\Requests\MergeCartsRequest;
use JayI\Polycart\Http\Requests\ShowCartRequest;
use JayI\Polycart\Http\Requests\StoreCartRequest;
use JayI\Polycart\Http\Requests\TransitionCartRequest;
use JayI\Polycart\Http\Requests\UpdateCartRequest;
use JayI\Polycart\Models\Cart;

final class CartController
{
    public function index(IndexCartsRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreCartRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function active(ActiveCartRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowCartRequest $request, Cart $cart): JsonResponse
    {
        return $request->persist();
    }

    public function activity(IndexActivityRequest $request, Cart $cart): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateCartRequest $request, Cart $cart): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DestroyCartRequest $request, Cart $cart): Response
    {
        return $request->persist();
    }

    public function clear(ClearCartRequest $request, Cart $cart): JsonResponse
    {
        return $request->persist();
    }

    public function transition(TransitionCartRequest $request, Cart $cart): JsonResponse
    {
        return $request->persist();
    }

    public function convert(ConvertCartRequest $request, Cart $cart): JsonResponse
    {
        return $request->persist();
    }

    public function merge(MergeCartsRequest $request, Cart $cart): JsonResponse
    {
        return $request->persist();
    }
}
