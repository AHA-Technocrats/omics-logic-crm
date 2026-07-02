<?php

namespace AHATechnocrats\Automation\Repositories;

use AHATechnocrats\Automation\Contracts\Webhook;
use AHATechnocrats\Core\Eloquent\Repository;

class WebhookRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model(): string
    {
        return Webhook::class;
    }
}
