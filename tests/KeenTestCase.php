<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests;

use JayI\Keen\KeenServiceProvider;

/**
 * The package with jayi/keen installed, so cart changes land in the
 * suite-wide audit log.
 */
abstract class KeenTestCase extends TestCase
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            KeenServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        parent::defineDatabaseMigrations();

        $this->loadMigrationsFrom(dirname(__DIR__).'/vendor/jayi/keen/database/migrations');
    }
}
