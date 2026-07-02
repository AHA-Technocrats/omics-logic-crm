<?php

use AHATechnocrats\Admin\Providers\ModuleServiceProvider as AdminModuleServiceProvider;
use AHATechnocrats\Attribute\Providers\ModuleServiceProvider as AttributeModuleServiceProvider;
use AHATechnocrats\Automation\Providers\ModuleServiceProvider as AutomationModuleServiceProvider;
use AHATechnocrats\Contact\Providers\ModuleServiceProvider as ContactModuleServiceProvider;
use AHATechnocrats\Core\Providers\ModuleServiceProvider as CoreModuleServiceProvider;
use AHATechnocrats\DataGrid\Providers\ModuleServiceProvider as DataGridModuleServiceProvider;
use AHATechnocrats\DataTransfer\Providers\ModuleServiceProvider as DataTransferModuleServiceProvider;
use AHATechnocrats\Email\Providers\ModuleServiceProvider as EmailModuleServiceProvider;
use AHATechnocrats\EmailTemplate\Providers\ModuleServiceProvider as EmailTemplateModuleServiceProvider;
use AHATechnocrats\Lead\Providers\ModuleServiceProvider as LeadModuleServiceProvider;
use AHATechnocrats\Product\Providers\ModuleServiceProvider as ProductModuleServiceProvider;
use AHATechnocrats\Quote\Providers\ModuleServiceProvider as QuoteModuleServiceProvider;
use AHATechnocrats\Tag\Providers\ModuleServiceProvider as TagModuleServiceProvider;
use AHATechnocrats\User\Providers\ModuleServiceProvider as UserModuleServiceProvider;
use AHATechnocrats\Warehouse\Providers\ModuleServiceProvider as WarehouseModuleServiceProvider;
use AHATechnocrats\WebForm\Providers\ModuleServiceProvider as WebFormModuleServiceProvider;

return [
    'modules' => [
        DataTransferModuleServiceProvider::class,
        AdminModuleServiceProvider::class,
        AttributeModuleServiceProvider::class,
        AutomationModuleServiceProvider::class,
        ContactModuleServiceProvider::class,
        CoreModuleServiceProvider::class,
        DataGridModuleServiceProvider::class,
        EmailTemplateModuleServiceProvider::class,
        EmailModuleServiceProvider::class,
        LeadModuleServiceProvider::class,
        ProductModuleServiceProvider::class,
        QuoteModuleServiceProvider::class,
        TagModuleServiceProvider::class,
        UserModuleServiceProvider::class,
        WarehouseModuleServiceProvider::class,
        WebFormModuleServiceProvider::class,
        DataTransferModuleServiceProvider::class,
    ],

    'register_route_models' => true,
];
