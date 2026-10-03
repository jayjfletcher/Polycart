<?php

declare(strict_types=1);

namespace Workbench\App\Carts;

use BackedEnum;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Types\CartType;

/**
 * A placed order: a buyer pays, the owner fulfils or refunds it.
 */
class OrderCart extends CartType
{
    public function statuses(): string
    {
        return OrderStatus::class;
    }

    public function transitions(): array
    {
        return [
            OrderStatus::PendingPayment->value => [OrderStatus::Paid, OrderStatus::Cancelled],
            OrderStatus::Paid->value => [OrderStatus::Fulfilled, OrderStatus::Refunded],
            OrderStatus::Fulfilled->value => [OrderStatus::Refunded],
        ];
    }

    public function roles(): array
    {
        return [
            'owner' => ['*'],
            'buyer' => ['view', 'update', 'checkout'],
            'viewer' => ['view'],
        ];
    }

    public function transitionAbility(?BackedEnum $from, BackedEnum $to): string
    {
        return $to === OrderStatus::Paid ? 'checkout' : 'transition';
    }

    public function requiresPrice(): bool
    {
        return true;
    }

    public function convertsTo(): array
    {
        return ['cart'];
    }

    public function convertedFrom(Cart $cart, Cart $source): void
    {
        $cart->meta = [...$cart->meta ?? [], 'order_number' => 'SO-'.strtoupper(substr($cart->id, -6))];
    }
}
