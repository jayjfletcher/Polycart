<?php

declare(strict_types=1);

namespace JayI\Polycart;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use JayI\Atrium\Facades\Atrium;
use JayI\Polycart\Atrium\PolycartPlugin;
use JayI\Polycart\Contracts\PriceResolver;
use JayI\Polycart\Cortex\CortexIntegration;
use JayI\Polycart\Mcp\PolycartServer;
use JayI\Polycart\Pricing\PurchasablePriceResolver;
use JayI\Polycart\Support\SourceContext;
use JayI\Polycart\Types\CartTypeRegistry;
use Laravel\Mcp\Facades\Mcp;

class PolycartServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/polycart.php', 'polycart');

        $this->app->singleton(CartTypeRegistry::class);

        // Bind your own to price lines from an ERP or a price book.
        $this->app->singleton(PriceResolver::class, PurchasablePriceResolver::class);

        $this->app->singleton(Polycart::class);

        // Scoped, so a source never outlives the request or job that set it.
        $this->app->scoped(SourceContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerRoutes();
        $this->registerMcpServer();
        $this->registerAtriumPlugin();

        // Cortex is optional: agents get the cart tools only when it is loaded.
        $this->app->make(CortexIntegration::class)->register();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'polycart');

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

    /**
     * The JSON API is off until an application turns it on, because it can
     * read and change every cart: put it behind your own auth middleware.
     */
    private function registerRoutes(): void
    {
        if ($this->app->make('config')->get('polycart.routes.enabled') !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/polycart.php');
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
    }
}
