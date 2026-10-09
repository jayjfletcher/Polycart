<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Actions;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\CartLine\Events\LinesAddedActionEvent;
use RefactorCircus\Polycart\Domains\CartLine\Events\LinesAddingActionEvent;
use RefactorCircus\Polycart\Domains\CartLine\Exceptions\LineRejectedException;
use RefactorCircus\Polycart\Domains\CartLine\Models\CartLineModel;

/**
 * Add several lines to a cart at once, all or nothing.
 *
 * Each line goes through the cart type's add pipeline in turn, inside one
 * transaction. If any is refused, none are written, and the refusal names the
 * line by its position in the list.
 */
final class AddLinesAction
{
    /**
     * The most lines one call may add.
     */
    public const int MAX_LINES = 100;

    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly AddLineAction $add,
    ) {}

    /**
     * Each line takes the same fields as a single add, under `lines.*`.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        $rules = ['lines' => ['required', 'array', 'list', 'min:1', 'max:'.self::MAX_LINES]];

        foreach (AddLineAction::rules() as $field => $fieldRules) {
            $rules['lines.*.'.$field] = array_map(
                fn (mixed $rule): mixed => is_string($rule) && str_starts_with($rule, 'required_with:')
                    ? 'required_with:lines.*.'.substr($rule, strlen('required_with:'))
                    : $rule,
                (array) $fieldRules,
            );
        }

        return $rules;
    }

    /**
     * @param  array<int, array{purchasable?: Model|null, quantity?: int, options?: array<string, mixed>, meta?: array<string, mixed>, unit_price?: int|null}>  $lines
     * @return Collection<int, CartLineModel>
     */
    public function execute(CartModel $cart, array $lines): Collection
    {
        LinesAddingActionEvent::dispatch($cart, $lines);

        $result = $this->perform($cart, $lines);

        LinesAddedActionEvent::dispatch($cart, $result);

        return $result;
    }

    /**
     * @param  array<int, array{purchasable?: Model|null, quantity?: int, options?: array<string, mixed>, meta?: array<string, mixed>, unit_price?: int|null}>  $lines
     * @return Collection<int, CartLineModel>
     */
    private function perform(CartModel $cart, array $lines): Collection
    {
        return $this->db->transaction(function () use ($cart, $lines): Collection {
            $added = new Collection;

            foreach (array_values($lines) as $index => $line) {
                try {
                    $added->push($this->add->execute(
                        $cart,
                        $line['purchasable'] ?? null,
                        $line['quantity'] ?? 1,
                        $line['options'] ?? [],
                        $line['meta'] ?? [],
                        $line['unit_price'] ?? null,
                    ));
                } catch (LineRejectedException $e) {
                    throw $e->atLine($index);
                }
            }

            return $added;
        });
    }
}
