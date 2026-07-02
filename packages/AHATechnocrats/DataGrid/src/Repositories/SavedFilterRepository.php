<?php

namespace AHATechnocrats\DataGrid\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;
use AHATechnocrats\DataGrid\Contracts\SavedFilter;

class SavedFilterRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return SavedFilter::class;
    }
}
