<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use JayI\Polycart\Http\Requests\DestroyMemberRequest;
use JayI\Polycart\Http\Requests\IndexMembersRequest;
use JayI\Polycart\Http\Requests\StoreMemberRequest;
use JayI\Polycart\Http\Requests\UpdateVisibilityRequest;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartMember;

final class CartMemberController
{
    public function index(IndexMembersRequest $request, Cart $cart): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreMemberRequest $request, Cart $cart): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DestroyMemberRequest $request, Cart $cart, CartMember $member): Response
    {
        return $request->persist();
    }

    public function visibility(UpdateVisibilityRequest $request, Cart $cart): JsonResponse
    {
        return $request->persist();
    }
}
