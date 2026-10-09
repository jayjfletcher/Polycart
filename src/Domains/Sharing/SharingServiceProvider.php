<?php

declare(strict_types=1);

namespace RefactorCircus\Polycart\Domains\Sharing;

use RefactorCircus\Keystone\Support\ServiceProvider;

class SharingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
