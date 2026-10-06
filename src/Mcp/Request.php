<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp;

use JayI\Foundation\Mcp\Requests\Request as FoundationRequest;
use JayI\Polycart\Domains\Activity\Enums\CartSource;
use JayI\Polycart\Domains\Activity\Services\SourceContext;
use JayI\Polycart\Domains\CartLine\Exceptions\LineRejectedException;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

/**
 * Base MCP request for Polycart's tools.
 *
 * Foundation's request does the authorization, validation and error
 * handling. This adds what only carts need: carts and activity recorded
 * during the call are stamped with the `mcp` source, and a rejected line
 * names its reason code so an agent can branch without parsing prose.
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
        $sources = app(SourceContext::class);
        $respond = fn (): Response|ResponseFactory => $this->respond($validated);

        try {
            // A caller that set a source already, such as a Cortex agent,
            // keeps it; otherwise this is an MCP client.
            return $sources->isSet() ? $respond() : $sources->using(CartSource::Mcp, $respond);
        } catch (LineRejectedException $e) {
            return Response::error(sprintf('%s (reason: %s)', $e->getMessage(), $e->reason));
        }
    }
}
