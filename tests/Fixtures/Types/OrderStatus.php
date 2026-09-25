<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Types;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Cancelled = 'cancelled';
}
