<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Models;

use Illuminate\Auth\Authenticatable as AuthenticatableConcern;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use JayI\Polycart\Concerns\HasCarts;
use JayI\Polycart\Contracts\CartParticipant;

/**
 * @property int $id
 * @property string $name
 * @property-read Collection<int, Team> $teams
 */
final class Person extends Model implements Authenticatable, AuthorizableContract, CartParticipant
{
    use AuthenticatableConcern;
    use Authorizable;
    use HasCarts;

    public $timestamps = false;

    protected $table = 'people';

    protected $guarded = [];

    public function cartScopes(): iterable
    {
        // Fresh every time, so joining or leaving a team applies at once.
        return $this->teams()->with('organization')->get()->all();
    }

    /**
     * @return BelongsToMany<Team, $this>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }
}
