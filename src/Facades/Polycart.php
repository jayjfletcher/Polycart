<?php

declare(strict_types=1);

namespace JayI\Polycart\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartMember;
use JayI\Polycart\Types\CartType;
use JayI\Polycart\Types\CartTypeRegistry;

/**
 * @method static CartTypeRegistry types()
 * @method static CartType type(string $key)
 * @method static Cart create(string $type, Model|string|null $owner = null, array<string, mixed> $attributes = [], Model|null $scope = null)
 * @method static Cart active(string $type, Model|string $owner, Model|null $scope = null)
 * @method static CartMember share(Cart $cart, Model $member, string $role)
 * @method static void unshare(Cart $cart, Model $member)
 * @method static mixed usingSource(\BackedEnum|string $source, \Closure $callback)
 * @method static Cart|null find(string $id)
 * @method static Cart convert(Cart $cart, string $to, bool $copy = true)
 * @method static Cart merge(Cart $from, Cart $into)
 *
 * @see \JayI\Polycart\Polycart
 */
class Polycart extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JayI\Polycart\Polycart::class;
    }
}
