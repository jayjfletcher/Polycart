<?php

declare(strict_types=1);

namespace JayI\Polycart\Atrium;

use JayI\Polycart\Models\Cart;

/**
 * Display helpers shared by the dashboard pages and widgets.
 */
final class Format
{
    /**
     * Minor units as a decimal amount, or a dash when there is no price.
     */
    public static function money(?int $minor): string
    {
        return $minor === null ? '—' : number_format($minor / 100, 2);
    }

    /**
     * Who a cart belongs to: its owner, a guest session, or nobody.
     */
    public static function owner(Cart $cart): string
    {
        if ($cart->owner_type !== null) {
            return class_basename($cart->owner_type).' #'.$cart->owner_id;
        }

        return $cart->session_key === null ? '—' : __('polycart::polycart.guest');
    }

    /**
     * Expired carts read as a warning; everything else is neutral.
     */
    public static function variant(Cart $cart): string
    {
        return $cart->isExpired() ? 'warning' : 'info';
    }
}
