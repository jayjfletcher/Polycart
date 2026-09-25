<?php

declare(strict_types=1);

namespace JayI\Polycart\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use JayI\Polycart\Models\Concerns\DispatchesModelEvents;

/**
 * Someone a cart is shared with, and the role the cart's type gives them.
 *
 * A member is a participant, such as a user, or a whole scope, such as a
 * team. A scope member covers whoever belongs to it when access is checked.
 *
 * @property string $id
 * @property string $cart_id
 * @property string $member_type
 * @property string $member_id
 * @property string $role
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Cart $cart
 * @property-read Model|null $member
 */
final class CartMember extends Model
{
    use DispatchesModelEvents;
    use HasUlids;

    protected $table = 'polycart_cart_members';

    protected $fillable = [
        'member_type',
        'member_id',
        'role',
    ];

    /**
     * @return BelongsTo<Cart, $this>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function member(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'member_type', 'member_id');
    }
}
