<?php

declare(strict_types=1);

namespace JayI\Polycart\Cortex;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use JayI\Foundation\Cortex\CortexIntegration;
use JayI\Polycart\Domains\Activity\Enums\CartSource;
use JayI\Polycart\Domains\Activity\Services\SourceContext;
use JayI\Polycart\Mcp\PolycartServer;
use Laravel\Ai\Events\InvokingTool;
use Laravel\Ai\Events\ToolFailed;
use Laravel\Ai\Events\ToolInvoked;
use Laravel\Ai\Tools\ToolNameResolver;

/**
 * Stamp carts and activity a Cortex agent records through a cart tool with
 * the `cortex` source, for exactly as long as the tool runs.
 *
 * Foundation's Cortex integration registers the server and tools and marks
 * the `cortex` surface for the audit log; Polycart's own activity log reads
 * its SourceContext instead, so this keeps it told too.
 */
final readonly class RecordsAgentCartSource
{
    public function __construct(
        private Container $app,
        private Dispatcher $events,
    ) {}

    /**
     * Listen for agent tool calls, when Cortex is active for Polycart.
     */
    public function register(CortexIntegration $cortex): void
    {
        if (! $cortex->active() || ! class_exists(InvokingTool::class)) {
            return;
        }

        $this->events->listen(InvokingTool::class, function (InvokingTool $event): void {
            if ($this->isCartTool(ToolNameResolver::resolve($event->tool))) {
                $this->app->make(SourceContext::class)->push(CartSource::Cortex);
            }
        });

        $leave = function (ToolInvoked|ToolFailed $event): void {
            if ($this->isCartTool(ToolNameResolver::resolve($event->tool))) {
                $this->app->make(SourceContext::class)->pop();
            }
        };

        $this->events->listen(ToolInvoked::class, $leave);
        $this->events->listen(ToolFailed::class, $leave);
    }

    private function isCartTool(string $name): bool
    {
        foreach (PolycartServer::TOOLS as $class) {
            if ($this->app->make($class)->name() === $name) {
                return true;
            }
        }

        return false;
    }
}
