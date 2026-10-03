<?php

declare(strict_types=1);

namespace Workbench\App\Carts;

use BackedEnum;
use Carbon\CarbonInterval;
use JayI\Polycart\Enums\Visibility;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Types\CartType;

/**
 * A priced quote a manager approves before it goes to the customer.
 */
class QuoteCart extends CartType
{
    public function statuses(): string
    {
        return QuoteStatus::class;
    }

    public function transitions(): array
    {
        return [
            QuoteStatus::Draft->value => [QuoteStatus::AwaitingApproval],
            QuoteStatus::AwaitingApproval->value => [QuoteStatus::Approved, QuoteStatus::Rejected],
            QuoteStatus::Rejected->value => [QuoteStatus::Draft],
            QuoteStatus::Approved->value => [QuoteStatus::Sent],
            QuoteStatus::Sent->value => [QuoteStatus::Accepted, QuoteStatus::Declined],
        ];
    }

    public function roles(): array
    {
        return [
            'owner' => ['*'],
            'approver' => ['view', 'approve'],
            'contributor' => ['view', 'update', 'submit'],
            'reader' => ['view'],
        ];
    }

    public function transitionAbility(?BackedEnum $from, BackedEnum $to): string
    {
        return match ($to) {
            QuoteStatus::AwaitingApproval => 'submit',
            QuoteStatus::Approved, QuoteStatus::Rejected => 'approve',
            default => 'transition',
        };
    }

    public function lifetime(): CarbonInterval
    {
        return CarbonInterval::days(60);
    }

    public function defaultVisibility(): Visibility
    {
        return Visibility::Scope;
    }

    public function requiresPrice(): bool
    {
        return true;
    }

    public function convertsTo(): array
    {
        return ['order'];
    }

    public function convertedFrom(Cart $cart, Cart $source): void
    {
        $cart->meta = [...$cart->meta ?? [], 'quote_number' => 'Q-'.strtoupper(substr($cart->id, -6))];
    }
}
