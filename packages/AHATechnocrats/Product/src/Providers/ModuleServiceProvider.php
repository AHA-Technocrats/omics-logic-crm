<?php

namespace AHATechnocrats\Product\Providers;

use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;
use AHATechnocrats\Product\Models\CampaignCategory;
use AHATechnocrats\Product\Models\Product;
use AHATechnocrats\Product\Models\ProductInventory;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        CampaignCategory::class,
        Product::class,
        ProductInventory::class,
    ];
}
