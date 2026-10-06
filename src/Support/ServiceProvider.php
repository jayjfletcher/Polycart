<?php

declare(strict_types=1);

namespace JayI\Polycart\Support;

use Closure;
use Illuminate\Support\Facades\Route;
use JayI\Foundation\Support\ServiceProvider as FoundationServiceProvider;
use JayI\Polycart\Domains\Activity\Enums\CartSource as CartSourceEnum;
use JayI\Polycart\Domains\Activity\Http\Middleware\CartSource;

/**
 * Base class for the Polycart domain service providers.
 *
 * Foundation's provider puts every JSON API route in one group - the
 * configured prefix and middleware and the `polycart.` name prefix. Polycart
 * adds the API source to it, so carts and activity recorded through the JSON
 * API say where they came from.
 */
abstract class ServiceProvider extends FoundationServiceProvider
{
    /**
     * Load a routes file inside the JSON API's route group.
     *
     * The JSON API is off until an application turns it on, because it can
     * read and change every cart: put it behind your own auth middleware.
     */
    protected function loadApiRoutesFrom(string|Closure $routes): void
    {
        parent::loadApiRoutesFrom(function () use ($routes): void {
            Route::middleware(CartSource::class.':'.CartSourceEnum::Api->value)->group($routes);
        });
    }
}
