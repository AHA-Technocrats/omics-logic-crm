<?php

namespace AHATechnocrats\EmailTemplate\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;

class EmailTemplateRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'AHATechnocrats\EmailTemplate\Contracts\EmailTemplate';
    }
}
