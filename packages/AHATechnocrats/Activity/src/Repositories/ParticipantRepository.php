<?php

namespace AHATechnocrats\Activity\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;

class ParticipantRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'AHATechnocrats\Activity\Contracts\Participant';
    }
}
