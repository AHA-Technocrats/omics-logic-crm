<?php

namespace AHATechnocrats\Admin\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'contacts.person.create.after' => [
            'AHATechnocrats\Admin\Listeners\Person@linkToEmail',
        ],

        'lead.create.after' => [
            'AHATechnocrats\Admin\Listeners\Lead@linkToEmail',
        ],

        'activity.create.after' => [
            'AHATechnocrats\Admin\Listeners\Activity@afterUpdateOrCreate',
        ],

        'activity.update.after' => [
            'AHATechnocrats\Admin\Listeners\Activity@afterUpdateOrCreate',
        ],
    ];
}
