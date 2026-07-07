<?php

namespace AHATechnocrats\Core\Models;

use AHATechnocrats\Core\Contracts\CoreConfig as CoreConfigContract;
use Illuminate\Database\Eloquent\Model;

class CoreConfig extends Model implements CoreConfigContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'core_config';

    protected $fillable = [
        'code',
        'value',
        'locale',
    ];

    protected $hidden = ['token'];
}
