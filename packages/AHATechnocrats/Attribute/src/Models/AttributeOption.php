<?php

namespace AHATechnocrats\Attribute\Models;

use AHATechnocrats\Attribute\Contracts\AttributeOption as AttributeOptionContract;
use Illuminate\Database\Eloquent\Model;

class AttributeOption extends Model implements AttributeOptionContract
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'sort_order',
        'attribute_id',
    ];

    /**
     * Get the attribute that owns the attribute option.
     */
    public function attribute()
    {
        return $this->belongsTo(AttributeProxy::modelClass());
    }
}
