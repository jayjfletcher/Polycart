<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use RefactorCircus\Polycart\Domains\Scope\Contracts\CartScope;

/**
 * A team inside an organization, where carts are started and shared.
 *
 * @property int $id
 * @property int $organization_id
 * @property string $name
 * @property-read Organization|null $organization
 */
#[Fillable(['organization_id', 'name'])]
class Team extends Model implements CartScope
{
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

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
