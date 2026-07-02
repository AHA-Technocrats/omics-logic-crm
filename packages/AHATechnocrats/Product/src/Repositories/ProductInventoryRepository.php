<?php

namespace AHATechnocrats\Product\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;

class ProductInventoryRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'AHATechnocrats\Product\Contracts\ProductInventory';
    }
}
