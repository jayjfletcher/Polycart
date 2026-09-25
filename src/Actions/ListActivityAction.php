<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use JayI\Polycart\Events\Action\ActivityListedActionEvent;
use JayI\Polycart\Events\Action\ActivityListingActionEvent;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartActivity;

/**
 * A cart's activity log, newest first.
 */
final class ListActivityAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'source' => ['sometimes', 'string', 'max:191'],
            'action' => ['sometimes', 'string', 'max:191'],
            'cursor' => ['sometimes', 'nullable', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:200'],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, CartActivity>
     */
    public function execute(Cart $cart, array $filters = []): CursorPaginator
    {
        ActivityListingActionEvent::dispatch($cart, $filters);

        $result = $this->perform($cart, $filters);

        ActivityListedActionEvent::dispatch($cart, $result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, CartActivity>
     */
    private function perform(Cart $cart, array $filters = []): CursorPaginator
    {
        $query = $cart->activities()->getQuery()
            ->reorder()
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        foreach (['source', 'action'] as $filter) {
            if (is_string($filters[$filter] ?? null)) {
                $query->where($filter, $filters[$filter]);
            }
        }

        return $query->cursorPaginate(
            perPage: isset($filters['per_page']) && is_numeric($filters['per_page']) ? (int) $filters['per_page'] : 50,
            cursor: is_string($filters['cursor'] ?? null) ? $filters['cursor'] : null,
        );
    }
}
