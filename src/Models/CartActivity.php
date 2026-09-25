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
 * One change to a cart: what happened, where from, and who did it.
 *
 * @property string $id
 * @property string $cart_id
 * @property string $action
 * @property string $source
 * @property string|null $actor_type
 * @property string|null $actor_id
 * @property array<string, mixed>|null $context
 * @property Carbon $created_at
 * @property-read Cart $cart
 * @property-read Model|null $actor
 */
final class CartActivity extends Model
{
    use DispatchesModelEvents;
    use HasUlids;

    public $timestamps = false;

    protected $table = 'polycart_cart_activities';

    /**
     * Microseconds keep entries in order when several land in one second.
     */
    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'action',
        'source',
        'actor_type',
        'actor_id',
        'context',
        'created_at',
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
    public function actor(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'actor_type', 'actor_id');
    }

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
