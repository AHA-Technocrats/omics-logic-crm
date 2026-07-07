<?php

namespace AHATechnocrats\OmicsLogic\Models;

use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    protected $table = 'omics_segments';

    protected $fillable = [
        'name',
        'description',
        'filter_query',
        'owner_id',
        'refresh_schedule',
        'last_refreshed_at',
        'contact_count_cached',
        'is_shared',
    ];

    protected $casts = [
        'filter_query' => 'array',
        'last_refreshed_at' => 'datetime',
        'is_shared' => 'boolean',
    ];
}
