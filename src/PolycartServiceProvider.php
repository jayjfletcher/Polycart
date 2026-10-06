<?php

declare(strict_types=1);

namespace JayI\Polycart;

use Illuminate\Support\Facades\Blade;
use JayI\Atrium\Facades\Atrium;
use JayI\Foundation\Cortex\CortexIntegration;
use JayI\Foundation\Packages\Package;
use JayI\Foundation\Support\PackageServiceProvider;
use JayI\Polycart\Atrium\PolycartPlugin;
use JayI\Polycart\Atrium\ScreenAccess;
use JayI\Polycart\Cortex\RecordsAgentCartSource;
use JayI\Polycart\Domains\DomainServiceProvider;
use JayI\Polycart\Mcp\PolycartServer;

class PolycartServiceProvider extends PackageServiceProvider
{
    /**
     * Polycart authorizes calls through the Gate unless an application turns
     * `polycart.authorization` off.
     */
    protected function definition(): Package
    {
        return Package::make('polycart', __NAMESPACE__)
            ->label('Polycart')
            ->server(PolycartServer::class)
            ->authorization();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/polycart.php', 'polycart');

        $this->registerPackage();

        $this->app->register(DomainServiceProvider::class);

        $this->app->singleton(Polycart::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The Cart policy is registered for the base model, so it covers
        // every type's subclass.
        $this->registerPolicies();
        $this->registerMcpServer();
        $this->registerAtriumPlugin(PolycartPlugin::class);
        $this->registerAtriumStyles();

        // Cortex is optional: agents get the cart tools only when it is loaded.
        $this->registerCortex();
        $this->app->make(RecordsAgentCartSource::class)->register(CortexIntegration::for($this->package()));

        $this->loadHistoryRoutes();

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
     * Utilities Polycart's screens use that Atrium's stylesheet lacks, when
     * the dashboard is mounted.
     */
    private function registerAtriumStyles(): void
    {
        if (! class_exists(Atrium::class) || $this->config()->get('polycart.ui.enabled') !== true) {
            return;
        }

        Atrium::css((string) file_get_contents(__DIR__.'/../resources/css/atrium.css'), 'polycart');
    }
}
