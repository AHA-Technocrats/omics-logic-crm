<?php

return [
    'leads' => [
        'name' => 'Leads',
        'repository' => 'AHATechnocrats\Lead\Repositories\LeadRepository',
        'label_column' => 'title',
    ],

    'lead_sources' => [
        'name' => 'Lead Sources',
        'repository' => 'AHATechnocrats\Lead\Repositories\SourceRepository',
    ],

    'lead_types' => [
        'name' => 'Lead Types',
        'repository' => 'AHATechnocrats\Lead\Repositories\TypeRepository',
    ],

    'lead_pipelines' => [
        'name' => 'Lead Pipelines',
        'repository' => 'AHATechnocrats\Lead\Repositories\PipelineRepository',
    ],

    'lead_pipeline_stages' => [
        'name' => 'Lead Pipeline Stages',
        'repository' => 'AHATechnocrats\Lead\Repositories\StageRepository',
    ],

    'users' => [
        'name' => 'Sales Owners',
        'repository' => 'AHATechnocrats\User\Repositories\UserRepository',
    ],

    'organizations' => [
        'name' => 'Organizations',
        'repository' => 'AHATechnocrats\Contact\Repositories\OrganizationRepository',
    ],

    'persons' => [
        'name' => 'Persons',
        'repository' => 'AHATechnocrats\Contact\Repositories\PersonRepository',
    ],

    'warehouses' => [
        'name' => 'Warehouses',
        'repository' => 'AHATechnocrats\Warehouse\Repositories\WarehouseRepository',
    ],

    'locations' => [
        'name' => 'Locations',
        'repository' => 'AHATechnocrats\Warehouse\Repositories\LocationRepository',
    ],
];
