<?php

namespace AHATechnocrats\OmicsLogic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConnectorSyncRun extends Model
{
    protected $table = 'omics_connector_sync_runs';

    protected $fillable = [
        'connector_id',
        'rows_total',
        'rows_new',
        'rows_merged',
        'rows_review',
        'rows_failed',
        'status',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function connector(): BelongsTo
    {
        return $this->belongsTo(Connector::class);
    }
}
