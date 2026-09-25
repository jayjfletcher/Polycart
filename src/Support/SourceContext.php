<?php

declare(strict_types=1);

namespace JayI\Polycart\Support;

use BackedEnum;
use Closure;
use Illuminate\Contracts\Config\Repository as Config;

/**
 * The source new carts are stamped with.
 *
 * Each surface sets it around the work it does — the JSON API, MCP, the
 * dashboard — and restores the previous one afterwards, so a source never
 * leaks into later work. Outside any of them, `polycart.default_source`
 * applies.
 */
final class SourceContext
{
    private ?string $current = null;

    public function __construct(private readonly Config $config) {}

    /**
     * Sources entered with push() and not yet left, innermost last.
     *
     * @var array<int, string|null>
     */
    private array $stack = [];

    public function current(): string
    {
        return $this->current ?? $this->config->string('polycart.default_source', 'code');
    }

    /**
     * Whether a source has been set, rather than falling back to the default.
     */
    public function isSet(): bool
    {
        return $this->current !== null;
    }

    /**
     * Enter a source until pop(), for work that starts and ends in separate
     * callbacks, such as an event pair.
     */
    public function push(BackedEnum|string $source): void
    {
        $this->stack[] = $this->current;
        $this->current = $source instanceof BackedEnum ? (string) $source->value : $source;
    }

    /**
     * Leave the source entered by the matching push().
     */
    public function pop(): void
    {
        if ($this->stack !== []) {
            $this->current = array_pop($this->stack);
        }
    }

    /**
     * Run a callback with carts created inside it stamped with a source.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function using(BackedEnum|string $source, Closure $callback): mixed
    {
        $previous = $this->current;
        $this->current = $source instanceof BackedEnum ? (string) $source->value : $source;

        try {
            return $callback();
        } finally {
            $this->current = $previous;
        }
    }
}
