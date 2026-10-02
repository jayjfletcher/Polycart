<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests;

use JayI\PennantPlus\PennantPlusServiceProvider;
use Laravel\Pennant\PennantServiceProvider;

/**
 * Polycart booted with jayi/pennantplus answering Atrium's feature checks.
 */
abstract class PennantPlusTestCase extends TestCase
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            PennantServiceProvider::class,
            PennantPlusServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('pennant.default', 'array');
    }
}
