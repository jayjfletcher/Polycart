<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use JayI\Polycart\Enums\Activity;
use JayI\Polycart\Events\Action\CartUnsharedActionEvent;
use JayI\Polycart\Events\Action\CartUnsharingActionEvent;
use JayI\Polycart\Exceptions\SharingException;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartMember;
use JayI\Polycart\Support\ActivityRecorder;

/**
 * Take a member off a cart. The last member holding the type's top role
 * cannot be removed, so a cart is never left without an owner.
 */
final class UnshareCartAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(Cart $cart, CartMember $member): void
    {
        $memberType = $member->member_type;
        $memberId = $member->member_id;

        CartUnsharingActionEvent::dispatch($cart, $member);

        $this->perform($cart, $member);

        CartUnsharedActionEvent::dispatch($cart, $memberType, $memberId);
    }

    private function perform(Cart $cart, CartMember $member): void
    {
        self::guardTopRole($cart, $member);

        $member->delete();
        $cart->unsetRelation('members');

        app(ActivityRecorder::class)->record($cart, Activity::Unshared, ['member_type' => $member->member_type, 'member_id' => $member->member_id]);
    }

    /**
     * Refuse to demote or remove the last member with the type's top role.
     *
     * @internal
     */
    public static function guardTopRole(Cart $cart, CartMember $member): void
    {
        $top = $cart->cartType()->creatorRole();

        if ($member->role === $top && $cart->members()->where('role', $top)->count() <= 1) {
            throw SharingException::lastOwner($cart->id);
        }
    }
}
