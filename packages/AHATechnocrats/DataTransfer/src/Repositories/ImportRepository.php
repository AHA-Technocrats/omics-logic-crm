<?php

namespace AHATechnocrats\DataTransfer\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;
use AHATechnocrats\DataTransfer\Contracts\Import;

class ImportRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Import::class;
    }
}
