<?php

namespace AHATechnocrats\Activity\Providers;

use AHATechnocrats\Activity\Models\Activity;
use AHATechnocrats\Activity\Models\File;
use AHATechnocrats\Activity\Models\Participant;
use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Activity::class,
        File::class,
        Participant::class,
    ];
}
