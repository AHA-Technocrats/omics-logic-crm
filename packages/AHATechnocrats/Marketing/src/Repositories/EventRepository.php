<?php

namespace AHATechnocrats\Marketing\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;
use AHATechnocrats\Marketing\Contracts\Event;

class EventRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model(): string
    {
        return Event::class;
    }
}
