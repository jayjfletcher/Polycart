<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Types;

use BackedEnum;
use JayI\Polycart\Types\CartType;

final class OrderCart extends CartType
{
    public function statuses(): string
    {
        return OrderStatus::class;
    }

    public function transitions(): array
    {
        return [
            OrderStatus::Pending->value => [OrderStatus::Processing, OrderStatus::Cancelled],
            OrderStatus::Processing->value => [OrderStatus::Shipped, OrderStatus::Cancelled],
        ];
    }

    /**
     * Orders separate paying from editing: a buyer can check out, an editor
     * can only change lines.
     */
    public function roles(): array
    {
        return [
            'owner' => ['*'],
            'buyer' => ['view', 'update', 'checkout'],
            'editor' => ['view', 'update'],
            'viewer' => ['view'],
        ];
    }

    public function transitionAbility(?BackedEnum $from, BackedEnum $to): string
    {
        return $to === OrderStatus::Processing ? 'checkout' : 'transition';
    }

    /**
     * An order can be reopened as a cart to reorder.
     */
    public function convertsTo(): array
    {
        return ['cart'];
    }

    public function requiresPrice(): bool
    {
        return true;
    }
}
