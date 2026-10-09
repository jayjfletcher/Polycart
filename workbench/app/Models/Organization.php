<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RefactorCircus\Polycart\Domains\Scope\Contracts\CartScope;

/**
 * The top of the demo's tree: carts are never shared across organizations.
 *
 * @property int $id
 * @property string $name
 */
#[Fillable(['name'])]
class Organization extends Model implements CartScope
{
    public function parentCartScope(): ?CartScope
    {
        return null;
    }

    /**
     * @return HasMany<Team, $this>
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }
}
