<?php

namespace AHATechnocrats\Tag\Providers;

use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;
use AHATechnocrats\Tag\Models\Tag;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Tag::class,
    ];
}
