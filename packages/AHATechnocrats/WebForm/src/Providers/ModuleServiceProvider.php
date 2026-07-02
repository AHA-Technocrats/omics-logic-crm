<?php

namespace AHATechnocrats\WebForm\Providers;

use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;
use AHATechnocrats\WebForm\Models\WebForm;
use AHATechnocrats\WebForm\Models\WebFormAttribute;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        WebForm::class,
        WebFormAttribute::class,
    ];
}
