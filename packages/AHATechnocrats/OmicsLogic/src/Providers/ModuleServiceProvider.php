<?php

namespace AHATechnocrats\OmicsLogic\Providers;

use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;
use AHATechnocrats\OmicsLogic\Models\Connector;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Connector::class,
    ];
}
