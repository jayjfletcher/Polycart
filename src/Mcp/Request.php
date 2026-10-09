<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Mcp;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Mcp\Requests\Request as KeystoneRequest;
use RefactorCircus\Polycart\Domains\CartLine\Exceptions\LineRejectedException;

/**
 * Base MCP request for Polycart's tools.
 *
 * Keystone's request does the authorization, validation and error
 * handling, and marks the call as coming from the `mcp` surface (or keeps
 * `cortex` for an agent). This adds what only carts need: a rejected line
 * names its reason code so an agent can branch without parsing prose.
 * Each request implements `respond()` with the validated input.
 */
abstract class Request extends KeystoneRequest
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
            return $this->respond($validated);
        } catch (LineRejectedException $e) {
            return Response::error(sprintf('%s (reason: %s)', $e->getMessage(), $e->reason));
        }
    }
}
