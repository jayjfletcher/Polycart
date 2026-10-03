<?php

declare(strict_types=1);

namespace JayI\Polycart;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use JayI\Atrium\Facades\Atrium;
use JayI\Polycart\Atrium\PolycartPlugin;
use JayI\Polycart\Atrium\ScreenAccess;
use JayI\Polycart\Cortex\CortexIntegration;
use JayI\Polycart\Domains\DomainServiceProvider;
use JayI\Polycart\Mcp\PolycartServer;
use Laravel\Mcp\Facades\Mcp;

class PolycartServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/polycart.php', 'polycart');

        $this->app->register(DomainServiceProvider::class);

        $this->app->singleton(Polycart::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerMcpServer();
        $this->registerAtriumPlugin();

        // Cortex is optional: agents get the cart tools only when it is loaded.
        $this->app->make(CortexIntegration::class)->register();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'polycart');

        // Dashboard views show a control only when its action is allowed,
        // asked exactly as the controller asks it.
        Blade::if('polycartCan', ScreenAccess::allows(...));

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'polycart');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/polycart.php' => config_path('polycart.php'),
        ], ['polycart', 'polycart-config']);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['polycart', 'polycart-migrations']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/polycart'),
        ], ['polycart', 'polycart-views']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/polycart'),
        ], ['polycart', 'polycart-lang']);
    }

    /**
     * The Cart policy is registered for the base model, so it covers every
     * type's subclass.
     */
    private function registerPolicies(): void
    {
        /** @var array<class-string, class-string> $policies */
        $policies = $this->app->make('config')->get('polycart.policies', []);

        foreach ($policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }

    private function registerMcpServer(): void
    {
        $config = $this->app->make('config');

        if ($config->get('polycart.mcp.web.enabled') === true) {
            /** @var array<int, string> $middleware */
            $middleware = $config->get('polycart.mcp.web.middleware', []);

            Mcp::web((string) $config->get('polycart.mcp.web.route'), PolycartServer::class)
                ->middleware($middleware);
        }

        if ($config->get('polycart.mcp.local.enabled') === true) {
            Mcp::local((string) $config->get('polycart.mcp.local.handle'), PolycartServer::class);
        }
    }

    /**
     * Atrium is optional: the dashboard mounts only when it is installed.
     */
    private function registerAtriumPlugin(): void
    {
        if (! class_exists(Atrium::class) || $this->app->make('config')->get('polycart.ui.enabled') !== true) {
            return;
        }

        Atrium::plugin(PolycartPlugin::class);

        // Utilities Polycart's screens use that Atrium's stylesheet lacks.
        Atrium::css((string) file_get_contents(__DIR__.'/../resources/css/atrium.css'), 'polycart');
    }
}
