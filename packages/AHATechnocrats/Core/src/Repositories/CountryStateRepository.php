<?php

namespace AHATechnocrats\Core\Repositories;

use Prettus\Repository\Traits\CacheableRepository;
use AHATechnocrats\Core\Eloquent\Repository;

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
