<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Contracts\CartScope;

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
