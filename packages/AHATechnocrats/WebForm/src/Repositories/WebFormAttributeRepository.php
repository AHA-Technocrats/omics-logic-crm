<?php

namespace AHATechnocrats\WebForm\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;

class WebFormAttributeRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'AHATechnocrats\WebForm\Contracts\WebFormAttribute';
    }
}
