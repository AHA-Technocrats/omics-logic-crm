<?php

use AHATechnocrats\Activity\Providers\ModuleServiceProvider;

return [
    'modules' => [
        ModuleServiceProvider::class,
        AHATechnocrats\Admin\Providers\ModuleServiceProvider::class,
        AHATechnocrats\Attribute\Providers\ModuleServiceProvider::class,
        AHATechnocrats\Automation\Providers\ModuleServiceProvider::class,
        AHATechnocrats\Contact\Providers\ModuleServiceProvider::class,
        AHATechnocrats\Core\Providers\ModuleServiceProvider::class,
        AHATechnocrats\DataGrid\Providers\ModuleServiceProvider::class,
        AHATechnocrats\EmailTemplate\Providers\ModuleServiceProvider::class,
        AHATechnocrats\Email\Providers\ModuleServiceProvider::class,
        AHATechnocrats\Lead\Providers\ModuleServiceProvider::class,
        AHATechnocrats\Product\Providers\ModuleServiceProvider::class,
        AHATechnocrats\Quote\Providers\ModuleServiceProvider::class,
        AHATechnocrats\Tag\Providers\ModuleServiceProvider::class,
        AHATechnocrats\User\Providers\ModuleServiceProvider::class,
        AHATechnocrats\Warehouse\Providers\ModuleServiceProvider::class,
        AHATechnocrats\WebForm\Providers\ModuleServiceProvider::class,
        AHATechnocrats\DataTransfer\Providers\ModuleServiceProvider::class,
    ],

    'register_route_models' => true,
];
