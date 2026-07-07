<?php

namespace AHATechnocrats\Lead\Models;

use AHATechnocrats\Lead\Contracts\Type as TypeContract;
use Illuminate\Database\Eloquent\Model;

class Type extends Model implements TypeContract
{
    protected $table = 'lead_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the leads.
     */
    public function leads()
    {
        return $this->hasMany(LeadProxy::modelClass());
    }
}
