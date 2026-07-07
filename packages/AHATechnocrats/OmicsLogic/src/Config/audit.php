<?php

use AHATechnocrats\Activity\Models\Activity;
use AHATechnocrats\Attribute\Models\Attribute;
use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\EmailTemplate\Models\EmailTemplate;
use AHATechnocrats\Lead\Models\Lead;
use AHATechnocrats\Lead\Models\Pipeline;
use AHATechnocrats\Lead\Models\Source;
use AHATechnocrats\Lead\Models\Type;
use AHATechnocrats\Marketing\Models\Campaign;
use AHATechnocrats\Marketing\Models\Event as MarketingEvent;
use AHATechnocrats\Product\Models\Product;
use AHATechnocrats\Tag\Models\Tag;
use AHATechnocrats\User\Models\Group;
use AHATechnocrats\User\Models\Role;
use AHATechnocrats\User\Models\User;
use AHATechnocrats\Warehouse\Models\Warehouse;
use AHATechnocrats\WebForm\Models\WebForm;

return [
    /*
    |--------------------------------------------------------------------------
    | Audit Logging Switch
    |--------------------------------------------------------------------------
    |
    | Master toggle for the automatic audit trail. When disabled, no lifecycle
    | events are recorded (manual AuditLogger::log() calls still work).
    |
    */
    'enabled' => (bool) env('AUDIT_LOG_ENABLED', true),

    /*
    | Record events fired from the console (seeders, queued jobs, artisan
    | commands). Off by default to avoid flooding the log during seeding.
    */
    'log_console' => (bool) env('AUDIT_LOG_CONSOLE', false),

    /*
    | Keys whose values are never persisted into before/after snapshots.
    | Matched case-insensitively against attribute names.
    */
    'redacted_keys' => [
        'password',
        'password_confirmation',
        'api_token',
        'token',
        'secret',
        'client_secret',
        'remember_token',
        'access_token',
        'refresh_token',
    ],

    /*
    | Columns stripped from every diff because they add noise rather than
    | meaning (timestamps flip on every write).
    */
    'ignored_keys' => [
        'created_at',
        'updated_at',
    ],

    /*
    |--------------------------------------------------------------------------
    | Entity Registry
    |--------------------------------------------------------------------------
    |
    | Declarative map of the business entities whose lifecycle should be
    | audited. For each entity:
    |
    |   label        Human label shown in the UI.
    |   model        Eloquent model — used to resolve the table for snapshots.
    |   label_field  Column used to build a friendly title for the record.
    |   events       Event-name prefixes. Either a single string prefix
    |                (expands to "<prefix>.create|update|delete.<before|after>")
    |                or an array keyed by operation for modules whose prefixes
    |                are inconsistent.
    |
    | Extend this list to audit additional modules — no code changes required.
    |
    */
    'entities' => [
        'lead' => [
            'label' => 'Lead',
            'model' => Lead::class,
            'label_field' => 'title',
            'events' => 'lead',
        ],

        'person' => [
            'label' => 'Contact',
            'model' => Person::class,
            'label_field' => 'name',
            'events' => 'contacts.person',
        ],

        'organization' => [
            'label' => 'Organization',
            'model' => Organization::class,
            'label_field' => 'name',
            'events' => [
                'create' => 'contacts.organization.create',
                'update' => 'contacts.organization.update',
                'delete' => 'contact.organization.delete',
            ],
        ],

        'activity' => [
            'label' => 'Activity',
            'model' => Activity::class,
            'label_field' => 'title',
            'events' => 'activity',
        ],

        'product' => [
            'label' => 'Product',
            'model' => Product::class,
            'label_field' => 'name',
            'events' => 'product',
        ],

        'user' => [
            'label' => 'User',
            'model' => User::class,
            'label_field' => 'name',
            'events' => 'settings.user',
        ],

        'role' => [
            'label' => 'Role',
            'model' => Role::class,
            'label_field' => 'name',
            'events' => 'settings.role',
        ],

        'group' => [
            'label' => 'Group',
            'model' => Group::class,
            'label_field' => 'name',
            'events' => 'settings.group',
        ],

        'attribute' => [
            'label' => 'Attribute',
            'model' => Attribute::class,
            'label_field' => 'name',
            'events' => 'settings.attribute',
        ],

        'source' => [
            'label' => 'Source',
            'model' => Source::class,
            'label_field' => 'name',
            'events' => 'settings.source',
        ],

        'type' => [
            'label' => 'Type',
            'model' => Type::class,
            'label_field' => 'name',
            'events' => 'settings.type',
        ],

        'pipeline' => [
            'label' => 'Pipeline',
            'model' => Pipeline::class,
            'label_field' => 'name',
            'events' => 'settings.pipeline',
        ],

        'email_template' => [
            'label' => 'Email Template',
            'model' => EmailTemplate::class,
            'label_field' => 'name',
            'events' => 'settings.email_templates',
        ],

        'tag' => [
            'label' => 'Tag',
            'model' => Tag::class,
            'label_field' => 'name',
            'events' => 'settings.tag',
        ],

        'marketing_campaign' => [
            'label' => 'Campaign',
            'model' => Campaign::class,
            'label_field' => 'name',
            'events' => 'settings.marketing.campaigns',
        ],

        'marketing_event' => [
            'label' => 'Marketing Event',
            'model' => MarketingEvent::class,
            'label_field' => 'name',
            'events' => 'settings.marketing.events',
        ],

        'warehouse' => [
            'label' => 'Warehouse',
            'model' => Warehouse::class,
            'label_field' => 'name',
            'events' => 'settings.warehouse',
        ],

        'web_form' => [
            'label' => 'Web Form',
            'model' => WebForm::class,
            'label_field' => 'name',
            'events' => 'settings.web_forms',
        ],
    ],
];
