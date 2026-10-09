<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use RefactorCircus\Polycart\Domains\Scope\Contracts\CartScope;

/**
 * @property int $id
 * @property string $name
 */
final class Organization extends Model implements CartScope
{
    public $timestamps = false;

    protected $guarded = [];

    public function parentCartScope(): ?CartScope
    {
        return null;
    }
}
