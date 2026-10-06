<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Scope;

use JayI\Foundation\Support\ServiceProvider;
use JayI\Polycart\Domains\Scope\Models\CartPathModel;

class ScopeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->keepMorphAliases([
            'JayI\Polycart\Models\CartPath' => CartPathModel::class,
        ]);
    }
}
