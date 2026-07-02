<?php

namespace AHATechnocrats\Warehouse\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;

class LocationRepository extends Repository
{
    /**
     * Searchable fields
     */
    protected $fieldSearchable = [
        'name',
        'warehouse_id',
    ];

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'AHATechnocrats\Warehouse\Contracts\Location';
    }
}
