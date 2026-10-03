<?php

declare(strict_types=1);

namespace Workbench\App\Carts;

enum CartStatus: string
{
    case Open = 'open';
    case CheckingOut = 'checking_out';
    case CheckedOut = 'checked_out';
    case Abandoned = 'abandoned';
}
