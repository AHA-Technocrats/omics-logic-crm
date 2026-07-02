<?php

namespace AHATechnocrats\Attribute\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;

class AttributeOptionRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'AHATechnocrats\Attribute\Contracts\AttributeOption';
    }
}
