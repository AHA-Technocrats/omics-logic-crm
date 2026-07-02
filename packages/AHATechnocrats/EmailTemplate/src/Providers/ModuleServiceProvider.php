<?php

namespace AHATechnocrats\EmailTemplate\Providers;

use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;
use AHATechnocrats\EmailTemplate\Models\EmailTemplate;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        EmailTemplate::class,
    ];
}
