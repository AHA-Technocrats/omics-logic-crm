<?php

namespace AHATechnocrats\User\Providers;

use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;
use AHATechnocrats\User\Models\Group;
use AHATechnocrats\User\Models\Role;
use AHATechnocrats\User\Models\User;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Group::class,
        Role::class,
        User::class,
    ];
}
