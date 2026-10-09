<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\CartLine\Actions;

use Illuminate\Database\ConnectionInterface;
use RefactorCircus\Polycart\Domains\Cart\Models\CartModel;
use RefactorCircus\Polycart\Domains\CartLine\Events\LinesUpdatedActionEvent;
use RefactorCircus\Polycart\Domains\CartLine\Events\LinesUpdatingActionEvent;
use RefactorCircus\Polycart\Domains\CartLine\Exceptions\LineRejectedException;
use RefactorCircus\Polycart\Domains\CartLine\Models\CartLineModel;
use RefactorCircus\Polycart\Domains\CartLine\Support\LineLookup;

/**
 * Change several lines' quantities or prices at once, all or nothing.
 *
 * A quantity of zero removes the line. If any change is refused — a line not
 * in this cart, a price the type requires — none are kept, and the refusal
 * names the entry by its position in the list.
 */
final class UpdateLinesAction
{
    public const int MAX_LINES = 100;

    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly UpdateLineAction $update,
    ) {}

    /**
     * Each entry names a line by `id` and takes the fields of a single update.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        $rules = [
            'lines' => ['required', 'array', 'list', 'min:1', 'max:'.self::MAX_LINES],
            'lines.*.id' => ['required', 'string', 'max:26'],
        ];

        foreach (UpdateLineAction::rules() as $field => $fieldRules) {
            $rules['lines.*.'.$field] = $fieldRules;
        }

        return $rules;
    }

    /**
     * @param  array<int, array{id: CartLineModel|string, quantity: int, unit_price?: int|null}>  $changes
     */
    public function execute(CartModel $cart, array $changes): CartModel
    {
        LinesUpdatingActionEvent::dispatch($cart, $changes);

        $result = $this->perform($cart, $changes);

        LinesUpdatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<int, array{id: CartLineModel|string, quantity: int, unit_price?: int|null}>  $changes
     */
    private function perform(CartModel $cart, array $changes): CartModel
    {
        $this->db->transaction(function () use ($cart, $changes): void {
            foreach (array_values($changes) as $index => $change) {
                try {
                    $this->update->execute(
                        LineLookup::in($cart, $change['id']),
                        $change['quantity'],
                        $change['unit_price'] ?? null,
                    );
                } catch (LineRejectedException $e) {
                    throw $e->atLine($index);
                }
            }
        });

        return $cart->unsetRelation('lines')->load('lines');
    }
}
