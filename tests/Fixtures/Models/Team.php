<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JayI\Polycart\Contracts\CartScope;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $name
 * @property-read Organization $organization
 */
final class Team extends Model implements CartScope
{
    public $timestamps = false;

    protected $guarded = [];

    public function parentCartScope(): ?CartScope
    {
        return $this->organization;
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
