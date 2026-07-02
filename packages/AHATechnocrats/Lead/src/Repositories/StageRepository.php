<?php

namespace AHATechnocrats\Lead\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;

class StageRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'AHATechnocrats\Lead\Contracts\Stage';
    }
}
