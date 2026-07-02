<?php

namespace AHATechnocrats\DataTransfer\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;
use AHATechnocrats\DataTransfer\Contracts\ImportBatch;

class ImportBatchRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return ImportBatch::class;
    }
}
