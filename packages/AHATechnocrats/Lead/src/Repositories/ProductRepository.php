<?php

namespace AHATechnocrats\Lead\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;

class ProductRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'AHATechnocrats\Lead\Contracts\Product';
    }
}
