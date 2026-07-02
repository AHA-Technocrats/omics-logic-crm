<?php

namespace AHATechnocrats\Core\Providers;

use AHATechnocrats\Core\Models\CoreConfig;
use AHATechnocrats\Core\Models\Country;
use AHATechnocrats\Core\Models\CountryState;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        CoreConfig::class,
        Country::class,
        CountryState::class,
    ];
}
