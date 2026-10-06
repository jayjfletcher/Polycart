<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Sharing;

use JayI\Foundation\Support\ServiceProvider;
use JayI\Polycart\Domains\Sharing\Models\CartMemberModel;

class SharingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->keepMorphAliases([
            'JayI\Polycart\Models\CartMember' => CartMemberModel::class,
        ]);

        $this->loadApiRoutesFrom(__DIR__.'/routes.php');
    }
}
