<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JayI\Polycart\Support\SourceContext;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stamp carts created during a request with a source.
 *
 * The package's own routes use it already. Add it to yours to tell your own
 * surfaces apart: `->middleware(CartSource::class.':web')`.
 */
final class CartSource
{
    public function __construct(private readonly SourceContext $sources) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $source): Response
    {
        return $this->sources->using($source, fn (): Response => $next($request));
    }
}
