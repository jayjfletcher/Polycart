<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use JayI\Polycart\Events\Action\CartsListedActionEvent;
use JayI\Polycart\Events\Action\CartsListingActionEvent;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Support\Morphs;

final class ListCartsAction
{
    public function __construct(private readonly Morphs $morphs) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', 'max:191'],
            'status' => ['sometimes', 'string', 'max:191'],
            'source' => ['sometimes', 'string', 'max:191'],
            'touched_by' => ['sometimes', 'string', 'max:191'],
            'owner_type' => ['nullable', 'string', 'max:191', 'required_with:owner_id'],
            'owner_id' => ['nullable', 'string', 'max:191', 'required_with:owner_type'],
            'session_key' => ['sometimes', 'string', 'max:191'],
            'parent' => ['sometimes', 'string', 'max:26'],
            'root' => ['sometimes', 'string', 'max:26'],
            'scope_type' => ['nullable', 'string', 'max:191', 'required_with:scope_id'],
            'scope_id' => ['nullable', 'string', 'max:191', 'required_with:scope_type'],
            'search' => ['sometimes', 'string', 'max:191'],
            'unexpired' => ['sometimes', 'boolean'],
            'cursor' => ['sometimes', 'nullable', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:200'],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  Model|null  $viewer  When given, only carts they can access.
     * @return CursorPaginator<int, Cart>
     */
    public function execute(array $filters = [], ?Model $viewer = null): CursorPaginator
    {
        CartsListingActionEvent::dispatch($filters, $viewer);

        $result = $this->perform($filters, $viewer);

        CartsListedActionEvent::dispatch($result, $viewer);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  Model|null  $viewer  When given, only carts they can access.
     * @return CursorPaginator<int, Cart>
     */
    private function perform(array $filters = [], ?Model $viewer = null): CursorPaginator
    {
        $query = Cart::query()->withCount('lines')->latest()->latest('id');

        if ($viewer !== null) {
            $query->accessibleBy($viewer);
        }

        if (is_string($filters['scope_type'] ?? null) && is_string($filters['scope_id'] ?? null)) {
            $query->whereHas('paths', fn (Builder $paths): Builder => $paths
                ->where('scope_type', $this->morphs->scopeMorphClass($filters['scope_type']))
                ->where('scope_id', $filters['scope_id']));
        }

        foreach (['type' => 'type', 'status' => 'status', 'source' => 'source', 'session_key' => 'session_key', 'parent' => 'parent_id', 'root' => 'root_id'] as $filter => $column) {
            if (is_string($filters[$filter] ?? null)) {
                $query->where($column, $filters[$filter]);
            }
        }

        if (is_string($filters['owner_type'] ?? null) && is_string($filters['owner_id'] ?? null)) {
            $query->where('owner_type', $this->morphs->ownerMorphClass($filters['owner_type']))
                ->where('owner_id', $filters['owner_id']);
        }

        if (is_string($filters['search'] ?? null)) {
            $search = $filters['search'];

            $query->where(fn (Builder $query): Builder => $query
                ->where('label', 'like', '%'.$search.'%')
                ->orWhere('id', 'like', $search.'%'));
        }

        if (is_string($filters['touched_by'] ?? null)) {
            $query->touchedBy($filters['touched_by']);
        }

        if (filter_var($filters['unexpired'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query->unexpired();
        }

        return $query->cursorPaginate(
            perPage: isset($filters['per_page']) && is_numeric($filters['per_page']) ? (int) $filters['per_page'] : 25,
            cursor: is_string($filters['cursor'] ?? null) ? $filters['cursor'] : null,
        );
    }
}
