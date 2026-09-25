<?php

declare(strict_types=1);

namespace JayI\Polycart\Pipeline;

use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Exceptions\LineRejectedException;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartLine;
use JayI\Polycart\Types\CartType;

/**
 * A line on its way into a cart, handed from one stage to the next.
 *
 * Stages read and change it: normalise the options, set the price, find the
 * line it merges into, build and write the CartLine. What arrived is kept in
 * the readonly properties; what the stages decide lives in the rest.
 */
final class PendingLine
{
    /**
     * The line this one merges into, when the cart already holds a match.
     */
    public ?CartLine $existing = null;

    /**
     * The line being written: the existing one with more quantity, or a new one.
     */
    public ?CartLine $line = null;

    /**
     * Identity used to find a matching line, set once options are final.
     */
    public ?string $fingerprint = null;

    /**
     * Anything a stage wants to hand to a later one.
     *
     * @var array<string, mixed>
     */
    public array $context = [];

    /**
     * @param  array<string, mixed>  $options
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public readonly Cart $cart,
        public readonly CartType $type,
        public ?Model $purchasable,
        public int $quantity,
        public array $options,
        public array $meta,
        public ?int $unitPrice,
        public readonly ?Model $actor,
        public readonly string $source,
    ) {}

    /**
     * Whether this add is going into a line the cart already holds.
     */
    public function merging(): bool
    {
        return $this->existing instanceof CartLine;
    }

    /**
     * The quantity the line will hold once written.
     */
    public function resultingQuantity(): int
    {
        return ($this->existing->quantity ?? 0) + $this->quantity;
    }

    /**
     * Stop the add. Nothing is written, and the caller gets the message and
     * the reason code.
     */
    public function reject(string $message, string $reason = 'rejected'): never
    {
        throw LineRejectedException::because($message, $reason);
    }
}
