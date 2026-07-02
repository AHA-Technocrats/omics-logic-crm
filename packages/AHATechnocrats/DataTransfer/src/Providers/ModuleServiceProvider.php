<?php

namespace AHATechnocrats\DataTransfer\Providers;

use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;
use AHATechnocrats\DataTransfer\Models\Import;
use AHATechnocrats\DataTransfer\Models\ImportBatch;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Define models to map with repository interfaces.
     *
     * @var array
     */
    protected $models = [
        Import::class,
        ImportBatch::class,
    ];
}
