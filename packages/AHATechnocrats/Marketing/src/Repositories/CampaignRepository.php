<?php

namespace AHATechnocrats\Marketing\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;
use AHATechnocrats\Marketing\Contracts\Campaign;

class CampaignRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model(): string
    {
        return Campaign::class;
    }
}
