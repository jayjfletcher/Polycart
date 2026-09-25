<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use JayI\Polycart\Access\Authorizer;
use JayI\Polycart\Enums\CartSource;
use JayI\Polycart\Exceptions\LineRejectedException;
use JayI\Polycart\Exceptions\PolycartException;
use JayI\Polycart\Support\SourceContext;
use Laravel\Mcp\Request as McpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

/**
 * Base MCP request.
 *
 * Mirrors the HTTP FormRequest `persist()` pattern so tools stay one line and
 * both surfaces resolve the same Actions. Parity is then structural rather
 * than something to maintain by hand.
 */
abstract class Request extends McpRequest
{
    final public function persist(): Response|ResponseFactory
    {
        try {
            if (! $this->authorize()) {
                return Response::error('Unauthorized.');
            }

            $sources = app(SourceContext::class);
            $handle = fn (): Response|ResponseFactory => $this->handle($this->validated());

            // A caller that set a source already, such as a Cortex agent,
            // keeps it; otherwise this is an MCP client.
            return $sources->isSet() ? $handle() : $sources->using(CartSource::Mcp, $handle);
        } catch (ModelNotFoundException) {
            return Response::error('Not found.');
        } catch (LineRejectedException $e) {
            // The reason code lets an agent branch without parsing prose.
            return Response::error(sprintf('%s (reason: %s)', $e->getMessage(), $e->reason));
        } catch (PolycartException $e) {
            // Polycart refusals say what the type allows, which an agent can
            // act on, so surface the message rather than a generic failure.
            return Response::error($e->getMessage());
        }
    }

    /**
     * Handle the validated tool call.
     *
     * @param  array<string, mixed>  $validated
     */
    abstract protected function handle(array $validated): Response|ResponseFactory;

    /**
     * Wrap a resolved collection in a `data` envelope.
     *
     * `Response::structured([])` throws, so an empty list must still ship
     * inside a non-empty `{ "data": [...] }` payload.
     *
     * @param  array<int, mixed>  $items
     * @param  array<string, mixed>  $meta
     */
    protected function structuredCollection(array $items, array $meta = []): ResponseFactory
    {
        return Response::structured(['data' => $items] + $meta);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [];
    }

    protected function authorize(): bool
    {
        return $this->authorizer()->authenticated($this->user());
    }

    protected function authorizer(): Authorizer
    {
        return app(Authorizer::class);
    }

    /**
     * The user calls act as, or null when authorization is off.
     */
    protected function actor(): ?Model
    {
        return $this->authorizer()->actor($this->user());
    }

    /**
     * Check an ability against the model's policy from `polycart.policies`.
     *
     * @param  Model|class-string<Model>  $subject
     * @param  array<int, mixed>  $arguments
     */
    protected function allows(string $ability, Model|string $subject, array $arguments = []): bool
    {
        return $this->authorizer()->can($this->user(), $ability, $subject, $arguments);
    }

    /**
     * Check an ability against every model given; an empty list passes.
     *
     * @param  iterable<int, Model>  $models
     */
    protected function allowsEach(string $ability, iterable $models): bool
    {
        foreach ($models as $model) {
            if (! $this->allows($ability, $model)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(): array
    {
        return Validator::validate($this->all(), $this->rules());
    }
}
