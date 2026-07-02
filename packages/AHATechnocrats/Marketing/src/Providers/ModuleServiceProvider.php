<?php

namespace AHATechnocrats\Marketing\Providers;

use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;
use AHATechnocrats\Marketing\Models\Campaign;
use AHATechnocrats\Marketing\Models\Event;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Define the module's array.
     *
     * @var array
     */
    protected $models = [
        Event::class,
        Campaign::class,
    ];
}
