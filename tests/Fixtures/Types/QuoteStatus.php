<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Types;

enum QuoteStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Accepted = 'accepted';
    case Declined = 'declined';
}
