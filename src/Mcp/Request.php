<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp;

use JayI\Foundation\Mcp\Requests\Request as FoundationRequest;
use JayI\Foundation\Support\Surface;
use JayI\Polycart\Domains\CartLine\Exceptions\LineRejectedException;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

/**
 * Base MCP request for Polycart's tools.
 *
 * Foundation's request does the authorization, validation and error
 * handling, and marks the call as coming from the `mcp` surface. This adds
 * what only carts need: a call a Cortex agent makes keeps the `cortex`
 * surface, and a rejected line names its reason code so an agent can branch
 * without parsing prose.
 * Each request implements `respond()` with the validated input.
 */
abstract class Request extends FoundationRequest
{
    /**
     * Handle the validated tool call.
     *
     * @param  array<string, mixed>  $validated
     */
    abstract protected function respond(array $validated): Response|ResponseFactory;

    /**
     * @param  array<string, mixed>  $validated
     */
    final protected function handle(array $validated): Response|ResponseFactory
    {
        try {
            return $this->agentCall()
                ? app(Surface::class)->using('cortex', fn (): Response|ResponseFactory => $this->respond($validated))
                : $this->respond($validated);
        } catch (LineRejectedException $e) {
            return Response::error(sprintf('%s (reason: %s)', $e->getMessage(), $e->reason));
        }
    }

    /**
     * Whether a Cortex agent made this call. Foundation's request marks every
     * call `mcp`, over the `cortex` surface its Cortex integration entered
     * around the tool, so look beneath it.
     */
    private function agentCall(): bool
    {
        $surface = app(Surface::class);

        $surface->leave();

        try {
            return $surface->current() === 'cortex';
        } finally {
            $surface->enter('mcp');
        }
    }
}
