<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Atrium;

use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;

/**
 * The one place Polycart's dashboard decides a status's colour.
 *
 * Each cart type names its own statuses, so they are read by meaning: `info`
 * is kept for pending - waiting on someone's decision - and used by nothing
 * else; `success` done or active, `warning` held, `danger` failed or
 * refused, `neutral` over. A status it does not recognise is in progress,
 * `primary`. An expired cart is over, whatever its status.
 */
final class Badges
{
    private const array VARIANTS = [
        'info' => ['pending', 'submitted', 'awaiting', 'awaiting_approval', 'awaiting_payment', 'awaiting_confirmation', 'in_review', 'review', 'requested', 'quoted'],
        'success' => ['open', 'active', 'accepted', 'approved', 'complete', 'completed', 'checked_out', 'checkout', 'paid', 'fulfilled', 'delivered', 'shipped', 'won', 'done'],
        'warning' => ['on_hold', 'held', 'hold', 'paused', 'suspended', 'backordered', 'disputed'],
        'danger' => ['declined', 'rejected', 'cancelled', 'canceled', 'failed', 'refused', 'revoked', 'void', 'voided', 'lost'],
        'neutral' => ['expired', 'abandoned', 'merged', 'converted', 'closed', 'archived', 'refunded'],
    ];

    /**
     * The colour of a cart's status dot.
     */
    public static function forCart(CartModel $cart): string
    {
        return $cart->isExpired() ? 'neutral' : self::forStatus((string) $cart->status);
    }

    /**
     * The colour of a status value, matched without regard to case, spaces
     * or dashes.
     */
    public static function forStatus(string $status): string
    {
        $key = str_replace([' ', '-'], '_', strtolower(trim($status)));

        foreach (self::VARIANTS as $variant => $statuses) {
            if (in_array($key, $statuses, true)) {
                return $variant;
            }
        }

        return 'primary';
    }
}
