<?php

namespace AHATechnocrats\Email\Providers;

use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;
use AHATechnocrats\Email\Models\Attachment;
use AHATechnocrats\Email\Models\Email;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Email::class,
        Attachment::class,
    ];
}
