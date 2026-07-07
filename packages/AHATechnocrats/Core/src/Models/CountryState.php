<?php

namespace AHATechnocrats\Core\Models;

use AHATechnocrats\Core\Contracts\CountryState as CountryStateContract;
use Illuminate\Database\Eloquent\Model;

class CountryState extends Model implements CountryStateContract
{
    public $timestamps = false;
}
