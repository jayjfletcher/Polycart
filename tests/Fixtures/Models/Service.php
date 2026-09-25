<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A model that does not implement Purchasable, so it is never priced.
 *
 * @property int $id
 */
final class Service extends Model
{
    protected $table = 'services';

    protected $guarded = [];
}
