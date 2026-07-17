<?php

namespace AHATechnocrats\OmicsLogic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalyticsUser extends Model
{
    protected $table = 'omics_analytics_users';

    protected $guarded = [];

    /**
     * @return HasMany<AnalyticsEnrollment>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(AnalyticsEnrollment::class, 'user_uid', 'uid');
    }
}
