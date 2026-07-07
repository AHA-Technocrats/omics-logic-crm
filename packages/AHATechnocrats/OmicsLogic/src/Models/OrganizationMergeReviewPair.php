<?php

namespace AHATechnocrats\OmicsLogic\Models;

use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\User\Models\UserProxy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationMergeReviewPair extends Model
{
    protected $table = 'omics_organization_merge_review_pairs';

    protected $fillable = [
        'organization_a_id',
        'organization_b_id',
        'confidence',
        'match_signals',
        'status',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'match_signals' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function organizationA(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_a_id');
    }

    public function organizationB(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_b_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'resolved_by');
    }
}
