<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Actions;

use Illuminate\Database\ConnectionInterface;
use JayI\Polycart\Domains\Activity\Enums\Activity;
use JayI\Polycart\Domains\Activity\Services\ActivityRecorder;
use JayI\Polycart\Domains\Cart\Events\CartsMergedActionEvent;
use JayI\Polycart\Domains\Cart\Events\CartsMergingActionEvent;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartLine\Actions\AddLineAction;
use JayI\Polycart\Domains\Scope\Exceptions\InvalidScopeException;

/**
 * Move every line of one cart into another, then delete the first.
 *
 * The usual case is a guest's session cart joining the customer's cart at
 * login. Lines go through the target's rules like any other add, so matching
 * lines merge and the target type can refuse what it does not accept.
 */
final class MergeCartsAction
{
    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly AddLineAction $add,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'into' => ['required', 'string', 'max:26'],
        ];
    }

    public function execute(CartModel $from, CartModel $into): CartModel
    {
        CartsMergingActionEvent::dispatch($from, $into);

        $result = $this->perform($from, $into);

        CartsMergedActionEvent::dispatch($from, $result);

        return $result;
    }

    private function perform(CartModel $from, CartModel $into): CartModel
    {
        if ($from->is($into)) {
            return $into;
        }

        // An unscoped cart, such as a guest's, can join any cart. A scoped
        // cart's lines stay inside its own scope.
        if ($from->isScoped() && ! $from->sharesScopeWith($into)) {
            throw InvalidScopeException::differentTree($from->id, $into->id);
        }

        $this->db->transaction(function () use ($from, $into): void {
            foreach ($from->lines()->with('purchasable')->get() as $line) {
                // The purchasable has since been deleted; there is nothing left to add.
                if (! $line->isCustom() && $line->purchasable === null) {
                    continue;
                }

                $this->add->execute(
                    $into,
                    $line->purchasable,
                    $line->quantity,
                    $line->options,
                    $line->meta,
                    $line->unit_price,
                );
            }

            // The lines carry their history with them.
            $recorder = app(ActivityRecorder::class);
            $recorder->record($from, Activity::MergedInto, ['into' => $into->id]);
            $recorder->inherit($from, $into);
            $recorder->record($into, Activity::Merged, ['from' => $from->id]);

            $from->delete();
        });

        return $into;
    }
}
