<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\CartLine\Actions;

use Illuminate\Database\ConnectionInterface;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartLine\Events\LinesRemovedActionEvent;
use JayI\Polycart\Domains\CartLine\Events\LinesRemovingActionEvent;
use JayI\Polycart\Domains\CartLine\Exceptions\LineRejectedException;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;
use JayI\Polycart\Domains\CartLine\Support\LineLookup;

/**
 * Remove several lines at once, all or nothing.
 *
 * Every line must belong to the cart. If one does not, none are removed and
 * the refusal names it by its position in the list.
 */
final class RemoveLinesAction
{
    public const int MAX_LINES = 100;

    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly RemoveLineAction $remove,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'lines' => ['required', 'array', 'list', 'min:1', 'max:'.self::MAX_LINES],
            'lines.*' => ['required', 'string', 'max:26', 'distinct'],
        ];
    }

    /**
     * @param  array<int, CartLineModel|string>  $lines  Lines, or their ids.
     */
    public function execute(CartModel $cart, array $lines): CartModel
    {
        LinesRemovingActionEvent::dispatch($cart, $lines);

        $result = $this->perform($cart, $lines);

        LinesRemovedActionEvent::dispatch($result, array_map(fn (CartLineModel|string $line): string => $line instanceof CartLineModel ? $line->id : $line, array_values($lines)));

        return $result;
    }

    /**
     * @param  array<int, CartLineModel|string>  $lines  Lines, or their ids.
     */
    private function perform(CartModel $cart, array $lines): CartModel
    {
        $this->db->transaction(function () use ($cart, $lines): void {
            foreach (array_values($lines) as $index => $line) {
                try {
                    $this->remove->execute(LineLookup::in($cart, $line));
                } catch (LineRejectedException $e) {
                    throw $e->atLine($index);
                }
            }
        });

        return $cart->unsetRelation('lines')->load('lines');
    }
}
