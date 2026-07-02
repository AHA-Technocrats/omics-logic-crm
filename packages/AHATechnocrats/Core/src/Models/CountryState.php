<?php

namespace AHATechnocrats\Core\Models;

use Illuminate\Database\Eloquent\Model;
use AHATechnocrats\Core\Contracts\CountryState as CountryStateContract;

class CountryState extends Model implements CountryStateContract
{
    public $timestamps = false;
}
