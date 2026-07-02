<?php

namespace AHATechnocrats\Product\Providers;

use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;
use AHATechnocrats\Product\Models\Product;
use AHATechnocrats\Product\Models\ProductInventory;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Product::class,
        ProductInventory::class,
    ];
}
