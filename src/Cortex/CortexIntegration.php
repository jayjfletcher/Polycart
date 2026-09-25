<?php

declare(strict_types=1);

namespace JayI\Polycart\Cortex;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use JayI\Cortex\CortexServiceProvider;
use JayI\Cortex\Mcp\McpInstructionOverrides;
use JayI\Cortex\Mcp\McpServerRegistry;
use JayI\Cortex\Tools\ToolDescriptionOverrides;
use JayI\Cortex\Tools\ToolRegistry;
use JayI\Polycart\Enums\CartSource;
use JayI\Polycart\Mcp\PolycartServer;
use JayI\Polycart\Support\SourceContext;
use Laravel\Ai\Events\InvokingTool;
use Laravel\Ai\Events\ToolFailed;
use Laravel\Ai\Events\ToolInvoked;
use Laravel\Ai\Tools\ToolNameResolver;
use Laravel\Mcp\Server\Tool;

/**
 * Connects Polycart's MCP server to Cortex, when Cortex is installed.
 *
 * - The server is registered with Cortex, so its instructions can be
 *   overridden with versioned, publishable content.
 * - Each tool is registered in Cortex's tool registry under its own name,
 *   so Cortex agents can manage carts, and its description can be
 *   overridden the same way.
 * - Changes an agent makes through a tool record `cortex` as their source.
 *
 * Cortex is optional. Nothing here runs unless its service provider is
 * loaded and `polycart.cortex.enabled` is true, and every Cortex class is
 * referenced only behind that check.
 */
final class CortexIntegration
{
    public function __construct(
        private readonly Application $app,
        private readonly Config $config,
    ) {}

    public function active(): bool
    {
        return $this->config->get('polycart.cortex.enabled', true) === true
            && class_exists(CortexServiceProvider::class)
            && $this->app->getProvider(CortexServiceProvider::class) !== null;
    }

    /**
     * Register with Cortex's registries as they are first resolved, so an
     * application that never touches Cortex pays nothing.
     */
    public function register(): void
    {
        if (! $this->active()) {
            return;
        }

        $this->app->afterResolving(McpServerRegistry::class, function (McpServerRegistry $servers): void {
            if (! $servers->has($this->serverName())) {
                $servers->register($this->serverName(), PolycartServer::class);
            }
        });

        $this->recordAgentCalls();

        $this->app->afterResolving(ToolRegistry::class, function (ToolRegistry $tools, Container $container): void {
            foreach ($this->tools() as $class) {
                $tool = $container->make($class);

                if ($tool instanceof Tool && ! $tools->has($tool->name())) {
                    $tools->register($tool->name(), $class);
                }
            }
        });
    }

    /**
     * Stamp changes an agent makes through a cart tool with the `cortex`
     * source, for exactly as long as the tool runs.
     */
    private function recordAgentCalls(): void
    {
        $events = $this->app->make(Dispatcher::class);

        $events->listen(InvokingTool::class, function (InvokingTool $event): void {
            if ($this->isCartTool(ToolNameResolver::resolve($event->tool))) {
                $this->app->make(SourceContext::class)->push(CartSource::Cortex);
            }
        });

        $leave = function (ToolInvoked|ToolFailed $event): void {
            if ($this->isCartTool(ToolNameResolver::resolve($event->tool))) {
                $this->app->make(SourceContext::class)->pop();
            }
        };

        $events->listen(ToolInvoked::class, $leave);
        $events->listen(ToolFailed::class, $leave);
    }

    private function isCartTool(string $name): bool
    {
        return in_array($name, $this->toolNames(), true);
    }

    /**
     * @return array<int, string>
     */
    private function toolNames(): array
    {
        return array_map(fn (string $class): string => $this->app->make($class)->name(), PolycartServer::TOOLS);
    }

    /**
     * The name the server is registered under in Cortex.
     */
    public function serverName(): string
    {
        return $this->config->string('polycart.cortex.server', 'polycart');
    }

    /**
     * The tools offered to Cortex: all of them, or those named in config.
     *
     * @return array<int, class-string<Tool>>
     */
    public function tools(): array
    {
        /** @var array<int, string>|null $only */
        $only = $this->config->get('polycart.cortex.tools');

        if ($only === null) {
            return PolycartServer::TOOLS;
        }

        return array_values(array_filter(
            PolycartServer::TOOLS,
            fn (string $class): bool => in_array($this->app->make($class)->name(), $only, true),
        ));
    }

    /**
     * The published instructions override for the server, if any.
     */
    public function instructions(): ?string
    {
        if (! $this->active()) {
            return null;
        }

        $name = $this->app->make(McpServerRegistry::class)->nameFor(PolycartServer::class);

        return $name === null ? null : $this->app->make(McpInstructionOverrides::class)->for($name);
    }

    /**
     * The published description override for a tool, if any.
     */
    public function description(string $tool): ?string
    {
        return $this->active() ? $this->app->make(ToolDescriptionOverrides::class)->for($tool) : null;
    }
}
