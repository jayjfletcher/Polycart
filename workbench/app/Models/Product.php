<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Contracts\Purchasable;
use JayI\Polycart\Models\Cart;

/**
 * Something the demo store sells, priced in cents.
 *
 * @property int $id
 * @property string $sku
 * @property string $name
 * @property int|null $price
 */
#[Fillable(['sku', 'name', 'price'])]
class Product extends Model implements Purchasable
{
    /**
     * A brass finish costs more; everything else is list price. A product
     * with no price is priced on request.
     */
    public function unitPriceFor(Cart $cart, array $options): ?int
    {
        if ($this->price === null) {
            return null;
        }

        return $this->price + (($options['finish'] ?? null) === 'brass' ? 1500 : 0);
    }
}
