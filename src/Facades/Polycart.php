<?php

declare(strict_types=1);

namespace JayI\Polycart\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartType\Services\CartTypeRegistry;
use JayI\Polycart\Domains\CartType\Support\CartType;
use JayI\Polycart\Domains\Sharing\Models\CartMemberModel;

/**
 * @method static CartTypeRegistry types()
 * @method static CartType type(string $key)
 * @method static CartModel create(string $type, Model|string|null $owner = null, array<string, mixed> $attributes = [], Model|null $scope = null)
 * @method static CartModel active(string $type, Model|string $owner, Model|null $scope = null)
 * @method static CartMemberModel share(CartModel $cart, Model $member, string $role)
 * @method static void unshare(CartModel $cart, Model $member)
 * @method static mixed usingSource(\BackedEnum|string $source, \Closure $callback)
 * @method static CartModel|null find(string $id)
 * @method static CartModel convert(CartModel $cart, string $to, bool $copy = true)
 * @method static CartModel merge(CartModel $from, CartModel $into)
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
