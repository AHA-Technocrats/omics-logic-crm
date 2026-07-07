<?php

namespace AHATechnocrats\Core\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;
use Prettus\Repository\Traits\CacheableRepository;

class CountryStateRepository extends Repository
{
    use CacheableRepository;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'AHATechnocrats\Core\Contracts\CountryState';
    }
}
