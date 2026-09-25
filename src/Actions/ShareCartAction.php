<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Access\ScopeTree;
use JayI\Polycart\Contracts\CartParticipant;
use JayI\Polycart\Contracts\CartScope;
use JayI\Polycart\Enums\Activity;
use JayI\Polycart\Events\Action\CartSharedActionEvent;
use JayI\Polycart\Events\Action\CartSharingActionEvent;
use JayI\Polycart\Exceptions\SharingException;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartMember;
use JayI\Polycart\Support\ActivityRecorder;

/**
 * Share a cart with someone, or a whole scope, inside the cart's boundary.
 *
 * Sharing with someone who is already a member changes their role.
 */
final class ShareCartAction
{
    public function __construct(private readonly ScopeTree $tree) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'member_type' => ['required', 'string', 'max:191'],
            'member_id' => ['required', 'string', 'max:191'],
            'role' => ['required', 'string', 'max:191'],
        ];
    }

    public function execute(Cart $cart, Model $member, string $role): CartMember
    {
        CartSharingActionEvent::dispatch($cart, $member, $role);

        $result = $this->perform($cart, $member, $role);

        CartSharedActionEvent::dispatch($cart, $result);

        return $result;
    }

    private function perform(Cart $cart, Model $member, string $role): CartMember
    {
        $type = $cart->cartType();

        if (! $type->shareable()) {
            throw SharingException::notShareable($type->key());
        }

        if (! array_key_exists($role, $type->roles())) {
            throw SharingException::unknownRole($type->key(), $role);
        }

        if (! $cart->isScoped()) {
            throw SharingException::unscoped($cart->id);
        }

        $inside = ($member instanceof CartParticipant || $member instanceof CartScope)
            && $this->tree->reaches($member, $cart->boundary_type, $cart->boundary_id);

        if (! $inside) {
            throw SharingException::outsideBoundary(ScopeTree::key($member), $cart->boundary_type.'|'.$cart->boundary_id);
        }

        [$memberType, $memberId] = ScopeTree::pair($member);

        $existing = $cart->members()->where('member_type', $memberType)->where('member_id', $memberId)->first();

        if ($existing instanceof CartMember && $existing->role !== $role) {
            UnshareCartAction::guardTopRole($cart, $existing);
        }

        $membership = $cart->members()->updateOrCreate(
            ['member_type' => $memberType, 'member_id' => $memberId],
            ['role' => $role],
        );

        $cart->unsetRelation('members');

        app(ActivityRecorder::class)->record($cart, Activity::Shared, ['member_type' => $memberType, 'member_id' => $memberId, 'role' => $role]);

        return $membership;
    }
}
