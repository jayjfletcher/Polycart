<?php

declare(strict_types=1);

namespace JayI\Polycart\Exceptions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JayI\Polycart\Models\CartLine;

/**
 * A line was refused: by a stage of the add pipeline, or by the cart type.
 *
 * The message is for people; `reason` is a stable code for programs, such
 * as `out_of_stock`, so a client can react without parsing prose.
 */
final class LineRejectedException extends PolycartException
{
    /**
     * @param  int|null  $index  The line's position in a batch add, from 0.
     */
    public function __construct(
        string $message,
        public readonly string $reason = 'rejected',
        public readonly ?int $index = null,
    ) {
        parent::__construct($message);
    }

    /**
     * The same refusal, naming the line's position in a batch.
     */
    public function atLine(int $index): self
    {
        return new self(sprintf('Line %d: %s', $index, $this->getMessage()), $this->reason, $index);
    }

    /**
     * Refuse a line with a message and a machine-readable reason.
     */
    public static function because(string $message, string $reason = 'rejected'): self
    {
        return new self($message, $reason);
    }

    public static function quantity(int $quantity): self
    {
        return new self(sprintf('A line quantity must be at least 1, [%d] given.', $quantity), 'invalid_quantity');
    }

    public static function notAccepted(string $type, ?Model $purchasable): self
    {
        return new self(sprintf(
            'A [%s] cart does not accept %s.',
            $type,
            $purchasable instanceof Model
                ? sprintf('[%s:%s]', $purchasable->getMorphClass(), (string) $purchasable->getKey())
                : 'custom lines',
        ), 'not_accepted');
    }

    public static function missingPrice(string $type, CartLine $line): self
    {
        return new self(sprintf(
            'Every line in a [%s] cart needs a unit price; [%s:%s] has none.',
            $type,
            $line->purchasable_type ?? 'custom',
            $line->purchasable_id ?? $line->fingerprint,
        ), 'missing_price');
    }

    public static function stopped(): self
    {
        return new self('A stage stopped the line without writing it or saying why.', 'stopped');
    }

    public function render(Request $request): JsonResponse|false
    {
        if (! $request->expectsJson()) {
            return false;
        }

        return new JsonResponse(array_filter([
            'message' => $this->getMessage(),
            'reason' => $this->reason,
            'line' => $this->index,
        ], fn (mixed $value): bool => $value !== null), 422);
    }
}
