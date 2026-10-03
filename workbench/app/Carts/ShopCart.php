<?php

declare(strict_types=1);

namespace Workbench\App\Carts;

use Carbon\CarbonInterval;
use JayI\Polycart\Types\CartType;

/**
 * The demo's storefront cart: filled, checked out, or left behind.
 */
class ShopCart extends CartType
{
    public function statuses(): string
    {
        return CartStatus::class;
    }

    public function transitions(): array
    {
        return [
            CartStatus::Open->value => [CartStatus::CheckingOut, CartStatus::Abandoned],
            CartStatus::CheckingOut->value => [CartStatus::CheckedOut, CartStatus::Open, CartStatus::Abandoned],
            CartStatus::Abandoned->value => [CartStatus::Open],
        ];
    }

    public function lifetime(): CarbonInterval
    {
        return CarbonInterval::days(30);
    }

    public function convertsTo(): array
    {
        return ['quote', 'order', 'wishlist'];
    }
}
