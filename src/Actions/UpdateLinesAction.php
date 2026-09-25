<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use Illuminate\Database\ConnectionInterface;
use JayI\Polycart\Events\Action\LinesUpdatedActionEvent;
use JayI\Polycart\Events\Action\LinesUpdatingActionEvent;
use JayI\Polycart\Exceptions\LineRejectedException;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartLine;
use JayI\Polycart\Support\LineLookup;

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
     * @param  array<int, array{id: CartLine|string, quantity: int, unit_price?: int|null}>  $changes
     */
    public function execute(Cart $cart, array $changes): Cart
    {
        LinesUpdatingActionEvent::dispatch($cart, $changes);

        $result = $this->perform($cart, $changes);

        LinesUpdatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<int, array{id: CartLine|string, quantity: int, unit_price?: int|null}>  $changes
     */
    private function perform(Cart $cart, array $changes): Cart
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
