<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartType\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Polycart\Domains\CartType\Http\Requests\IndexCartTypesRequest;

final class CartTypeController
{
    public function index(IndexCartTypesRequest $request): JsonResponse
    {
        return $request->persist();
    }
}
