<?php

namespace AHATechnocrats\Quote\Providers;

use AHATechnocrats\Core\Providers\BaseModuleServiceProvider;
use AHATechnocrats\Quote\Models\Quote;
use AHATechnocrats\Quote\Models\QuoteItem;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Quote::class,
        QuoteItem::class,
    ];
}
