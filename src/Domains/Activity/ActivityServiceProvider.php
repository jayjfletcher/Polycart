<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Activity;

use JayI\Polycart\Domains\Activity\Models\CartActivityModel;
use JayI\Polycart\Domains\Activity\Services\SourceContext;
use JayI\Polycart\Support\ServiceProvider;

class ActivityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Scoped, so a source never outlives the request or job that set it.
        $this->app->scoped(SourceContext::class);
    }

    public function boot(): void
    {
        $this->keepMorphAliases([
            'JayI\Polycart\Models\CartActivity' => CartActivityModel::class,
        ]);

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
