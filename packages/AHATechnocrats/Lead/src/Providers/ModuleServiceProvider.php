<?php

namespace AHATechnocrats\Lead\Providers;

use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;
use AHATechnocrats\Lead\Models\Lead;
use AHATechnocrats\Lead\Models\Pipeline;
use AHATechnocrats\Lead\Models\Product;
use AHATechnocrats\Lead\Models\Source;
use AHATechnocrats\Lead\Models\Stage;
use AHATechnocrats\Lead\Models\Type;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Lead::class,
        Pipeline::class,
        Product::class,
        Source::class,
        Stage::class,
        Type::class,
    ];
}
