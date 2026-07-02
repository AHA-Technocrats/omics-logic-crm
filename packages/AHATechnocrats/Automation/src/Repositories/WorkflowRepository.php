<?php

namespace AHATechnocrats\Automation\Repositories;

use AHATechnocrats\Automation\Contracts\Workflow;
use AHATechnocrats\Core\Eloquent\Repository;

class WorkflowRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model(): string
    {
        return Workflow::class;
    }
}
