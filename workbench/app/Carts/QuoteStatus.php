<?php

declare(strict_types=1);

namespace Workbench\App\Carts;

enum QuoteStatus: string
{
    case Draft = 'draft';
    case AwaitingApproval = 'awaiting_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Declined = 'declined';
}
