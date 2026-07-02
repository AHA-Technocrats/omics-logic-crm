<?php

namespace AHATechnocrats\Automation\Providers;

use AHATechnocrats\Automation\Models\Webhook;
use AHATechnocrats\Automation\Models\Workflow;
use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Define the modals to map with this module.
     *
     * @var array
     */
    protected $models = [
        Workflow::class,
        Webhook::class,
    ];
}
