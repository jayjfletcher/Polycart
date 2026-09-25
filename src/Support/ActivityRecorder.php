<?php

declare(strict_types=1);

namespace JayI\Polycart\Support;

use BackedEnum;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartActivity;

/**
 * Writes a cart's activity log and keeps its list of sources.
 *
 * Every entry takes the current source from SourceContext and the signed-in
 * user, when there is one, as its actor. The cart's `sources` column holds
 * each distinct source once, in the order they first touched it.
 */
final class ActivityRecorder
{
    public function __construct(
        private readonly SourceContext $sources,
        private readonly Auth $auth,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function record(Cart $cart, BackedEnum|string $action, array $context = []): CartActivity
    {
        $actor = $this->actor();
        $source = $this->sources->current();

        $activity = $cart->activities()->create([
            'action' => $action instanceof BackedEnum ? (string) $action->value : $action,
            'source' => $source,
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor === null ? null : (string) $actor->getKey(),
            'context' => $context === [] ? null : $context,
            'created_at' => Carbon::now(),
        ]);

        $this->addSources($cart, [$source]);

        return $activity;
    }

    /**
     * Carry one cart's history onto another, when its lines or its whole
     * self flow into it through a copy or a merge.
     */
    public function inherit(Cart $from, Cart $into): void
    {
        foreach ($from->activities()->get() as $activity) {
            $into->activities()->create($activity->only([
                'action', 'source', 'actor_type', 'actor_id', 'context', 'created_at',
            ]));
        }

        // Rebuilt from the log so the order stays the order each source
        // first touched what is now this cart, however the histories met.
        $this->writeSources($into, $into->activities()->pluck('source')->unique()->values()->all());
    }

    /**
     * @param  array<int, string>  $sources
     */
    private function addSources(Cart $cart, array $sources): void
    {
        $current = $cart->sources ?? [];
        $merged = array_values(array_unique([...$current, ...$sources]));

        if ($merged !== $current) {
            $this->writeSources($cart, $merged);
        }
    }

    /**
     * @param  array<int, string>  $merged
     */
    private function writeSources(Cart $cart, array $merged): void
    {
        // Written straight to the row: recording must not touch the cart's
        // timestamps or fire its model events.
        Cart::query()->withoutGlobalScopes()->withTrashed()->whereKey($cart->id)->update([
            'sources' => json_encode($merged, JSON_THROW_ON_ERROR),
        ]);

        $cart->forceFill(['sources' => $merged])->syncOriginalAttribute('sources');
    }

    private function actor(): ?Model
    {
        $user = $this->auth->guard()->user();

        return $user instanceof Model ? $user : null;
    }
}
