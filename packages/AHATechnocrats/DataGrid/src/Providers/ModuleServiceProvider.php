<?php

namespace AHATechnocrats\DataGrid\Providers;

use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;
use AHATechnocrats\DataGrid\Models\SavedFilter;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        SavedFilter::class,
    ];
}
