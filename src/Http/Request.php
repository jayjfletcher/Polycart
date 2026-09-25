<?php

declare(strict_types=1);

namespace JayI\Polycart\Http;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use JayI\Polycart\Access\Authorizer;
use JayI\Polycart\Models\Cart;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base HTTP request.
 *
 * Validation rules come from the Action the request wraps, and `persist()`
 * calls that same Action. The MCP surface does the same, so both speak to one
 * implementation rather than two that drift.
 *
 * With `polycart.authorization` on, every request acts as the authenticated
 * user and each change goes through the Gate.
 */
abstract class Request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->authorizer()->authenticated($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Execute the request's use case and build the response.
     */
    abstract public function persist(): Response;

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
     * @param  array<int, mixed>  $arguments
     */
    protected function allows(string $ability, Cart $cart, array $arguments = []): bool
    {
        return $this->authorizer()->can($this->user(), $ability, $cart, $arguments);
    }
}
