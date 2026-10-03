<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartLine\Contracts\Purchasable;

/**
 * @property int $id
 * @property string $sku
 * @property int|null $price
 * @property int|null $stock
 */
final class Product extends Model implements Purchasable
{
    protected $table = 'products';

    protected $guarded = [];

    public function unitPriceFor(CartModel $cart, array $options): ?int
    {
        $surcharge = ($options['finish'] ?? null) === 'gold' ? 500 : 0;

        return $this->price === null ? null : $this->price + $surcharge;
    }
}
