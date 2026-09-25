<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Polycart\Http\Requests\IndexCartTypesRequest;

final class CartTypeController
{
    public function index(IndexCartTypesRequest $request): JsonResponse
    {
        return $request->persist();
    }
}
