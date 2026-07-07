<?php

namespace AHATechnocrats\Core\Models;

use AHATechnocrats\Core\Contracts\Country as CountryContract;
use Illuminate\Database\Eloquent\Model;

class Country extends Model implements CountryContract
{
    public $timestamps = false;
}
