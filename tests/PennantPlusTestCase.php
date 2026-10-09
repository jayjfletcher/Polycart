<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Tests;

use Laravel\Pennant\PennantServiceProvider;
use RefactorCircus\PennantPlus\PennantPlusServiceProvider;

/**
 * Polycart booted with refactor-circus/pennantplus answering Atrium's feature checks.
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
