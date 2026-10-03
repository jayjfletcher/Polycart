<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Sharing\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\Sharing\Http\Requests\DestroyMemberRequest;
use JayI\Polycart\Domains\Sharing\Http\Requests\IndexMembersRequest;
use JayI\Polycart\Domains\Sharing\Http\Requests\StoreMemberRequest;
use JayI\Polycart\Domains\Sharing\Http\Requests\UpdateVisibilityRequest;
use JayI\Polycart\Domains\Sharing\Models\CartMemberModel;

final class CartMemberController
{
    public function index(IndexMembersRequest $request, CartModel $cart): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreMemberRequest $request, CartModel $cart): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DestroyMemberRequest $request, CartModel $cart, CartMemberModel $member): Response
    {
        return $request->persist();
    }

    public function visibility(UpdateVisibilityRequest $request, CartModel $cart): JsonResponse
    {
        return $request->persist();
    }
}
