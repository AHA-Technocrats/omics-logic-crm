<?php

namespace AHATechnocrats\OmicsLogic\Models;

use AHATechnocrats\OmicsLogic\Contracts\Connector as ConnectorContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Connector extends Model implements ConnectorContract
{
    protected $table = 'omics_connectors';

    protected $fillable = [
        'type',
        'name',
        'config',
        'status',
        'last_sync_at',
        'last_sync_status',
        'last_error',
    ];

    protected $casts = [
        'config' => 'array',
        'last_sync_at' => 'datetime',
    ];

    public function syncRuns(): HasMany
    {
        return $this->hasMany(ConnectorSyncRun::class);
    }
}
