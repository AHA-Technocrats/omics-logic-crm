<?php

namespace AHATechnocrats\Warehouse\Providers;

use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;
use AHATechnocrats\Warehouse\Models\Location;
use AHATechnocrats\Warehouse\Models\Warehouse;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Location::class,
        Warehouse::class,
    ];
}
