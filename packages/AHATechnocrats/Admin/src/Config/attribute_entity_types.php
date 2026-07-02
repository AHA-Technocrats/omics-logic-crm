<?php

return [
    'leads' => [
        'name' => 'admin::app.leads.index.title',
        'repository' => 'AHATechnocrats\Lead\Repositories\LeadRepository',
    ],

    'persons' => [
        'name' => 'admin::app.contacts.persons.index.title',
        'repository' => 'AHATechnocrats\Contact\Repositories\PersonRepository',
    ],

    'organizations' => [
        'name' => 'admin::app.contacts.organizations.index.title',
        'repository' => 'AHATechnocrats\Contact\Repositories\OrganizationRepository',
    ],

    'products' => [
        'name' => 'admin::app.products.index.title',
        'repository' => 'AHATechnocrats\Product\Repositories\ProductRepository',
    ],

    'quotes' => [
        'name' => 'admin::app.quotes.index.title',
        'repository' => 'AHATechnocrats\Quote\Repositories\QuoteRepository',
    ],

    'warehouses' => [
        'name' => 'admin::app.settings.warehouses.index.title',
        'repository' => 'AHATechnocrats\Warehouse\Repositories\WarehouseRepository',
    ],
];
