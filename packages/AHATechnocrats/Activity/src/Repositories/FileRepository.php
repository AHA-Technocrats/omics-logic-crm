<?php

namespace AHATechnocrats\Activity\Repositories;

use AHATechnocrats\Activity\Contracts\File;
use AHATechnocrats\Core\Eloquent\Repository;

class FileRepository extends Repository
{
    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return File::class;
    }
}
