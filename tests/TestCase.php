<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests;

use JayI\Atrium\AtriumServiceProvider;
use JayI\Polycart\PolycartServiceProvider;
use JayI\Polycart\Tests\Fixtures\Models\Organization;
use JayI\Polycart\Tests\Fixtures\Models\Person;
use JayI\Polycart\Tests\Fixtures\Models\Product;
use JayI\Polycart\Tests\Fixtures\Models\Team;
use JayI\Polycart\Tests\Fixtures\Types\AuditedCart;
use JayI\Polycart\Tests\Fixtures\Types\CarelessCart;
use JayI\Polycart\Tests\Fixtures\Types\OpeningCart;
use JayI\Polycart\Tests\Fixtures\Types\OrderCart;
use JayI\Polycart\Tests\Fixtures\Types\ProjectCart;
use JayI\Polycart\Tests\Fixtures\Types\QuoteCart;
use JayI\Polycart\Tests\Fixtures\Types\RetailCart;
use JayI\Polycart\Tests\Fixtures\Types\SavedCart;
use JayI\Polycart\Tests\Fixtures\Types\SetCart;
use JayI\Polycart\Tests\Fixtures\Types\WholesaleCart;
use Laravel\Mcp\Server\McpServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Workbench\App\Models\User;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            McpServiceProvider::class,
            AtriumServiceProvider::class,
            PolycartServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('polycart.routes.enabled', true);
        // Surface tests cover behaviour; AccessTest turns authorization on.
        $app['config']->set('polycart.authorization', false);
        $app['config']->set('polycart.owners', ['user' => User::class, 'person' => Person::class]);
        $app['config']->set('polycart.scopes', ['team' => Team::class, 'organization' => Organization::class]);
        $app['config']->set('polycart.purchasables', ['product' => Product::class]);
        $app['config']->set('database.connections.testing.foreign_key_constraints', true);
        $app['config']->set('polycart.types', [
            'cart' => RetailCart::class,
            'saved' => SavedCart::class,
            'quote' => QuoteCart::class,
            'order' => OrderCart::class,
            'project' => ProjectCart::class,
            'set' => SetCart::class,
            'opening' => OpeningCart::class,
            'wholesale' => WholesaleCart::class,
            'audited' => AuditedCart::class,
            'careless' => CarelessCart::class,
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        // Laravel's own migrations give the owner tests a real user to attach.
        $this->loadLaravelMigrations();

        $this->loadMigrationsFrom(dirname(__DIR__).'/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/Fixtures/migrations');
    }
}
