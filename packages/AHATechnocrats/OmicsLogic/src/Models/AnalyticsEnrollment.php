<?php

namespace AHATechnocrats\OmicsLogic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEnrollment extends Model
{
    protected $table = 'omics_analytics_enrollments';

    protected $guarded = [];

    protected $casts = [
        'purchased_at' => 'datetime',
        'amount' => 'decimal:4',
        'rating' => 'integer',
    ];

    /**
     * @return BelongsTo<AnalyticsUser, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(AnalyticsUser::class, 'user_uid', 'uid');
    }
}
