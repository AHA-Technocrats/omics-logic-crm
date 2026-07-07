<?php

namespace AHATechnocrats\Attribute\Models;

use AHATechnocrats\Attribute\Contracts\Attribute as AttributeContract;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model implements AttributeContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'type',
        'entity_type',
        'lookup_type',
        'is_required',
        'is_unique',
        'quick_add',
        'validation',
        'is_user_defined',
    ];

    /**
     * Get the options.
     */
    public function options()
    {
        return $this->hasMany(AttributeOptionProxy::modelClass());
    }
}
