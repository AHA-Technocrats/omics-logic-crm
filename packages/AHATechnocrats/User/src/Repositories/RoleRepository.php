<?php

namespace AHATechnocrats\User\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;

class RoleRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'AHATechnocrats\User\Contracts\Role';
    }
}
