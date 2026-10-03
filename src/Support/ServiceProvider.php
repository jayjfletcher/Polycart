<?php

declare(strict_types=1);

namespace JayI\Polycart\Support;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use JayI\Polycart\Domains\Activity\Enums\CartSource as CartSourceEnum;
use JayI\Polycart\Domains\Activity\Http\Middleware\CartSource;

/**
 * Base class for the Polycart domain service providers.
 *
 * Every JSON API route shares one group - the configured prefix and
 * middleware, the API source, and the `polycart.` name prefix - so each
 * domain loads its routes file through `loadApiRoutesFrom()` rather than
 * building the group itself.
 */
abstract class ServiceProvider extends BaseServiceProvider
{
    /**
     * Load a routes file inside the JSON API's route group.
     *
     * The JSON API is off until an application turns it on, because it can
     * read and change every cart: put it behind your own auth middleware.
     */
    protected function loadApiRoutesFrom(string $path): void
    {
        $config = $this->app->make(Repository::class);

        if ($config->get('polycart.routes.enabled') !== true || $this->routesAreCached()) {
            return;
        }

        /** @var string $prefix */
        $prefix = $config->get('polycart.routes.prefix');

        /** @var array<int, string> $middleware */
        $middleware = $config->get('polycart.routes.middleware');

        Route::prefix($prefix)
            ->middleware([...$middleware, CartSource::class.':'.CartSourceEnum::Api->value])
            ->name('polycart.')
            ->group($path);
    }

    /**
     * Keep the class names models were stored under before they moved into
     * their domain, so any polymorphic `*_type` column, audit trail or other
     * record an application wrote with the old names still resolves - and new
     * records keep writing the same value.
     *
     * @param  array<string, class-string<Model>>  $map
     */
    protected function keepMorphAliases(array $map): void
    {
        Relation::morphMap($map);
    }

    protected function routesAreCached(): bool
    {
        return $this->app instanceof CachesRoutes && $this->app->routesAreCached();
    }
}
