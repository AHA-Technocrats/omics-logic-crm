<?php

namespace AHATechnocrats\OmicsLogic\Models;

use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\User\Models\UserProxy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MergeReviewPair extends Model
{
    protected $table = 'omics_merge_review_pairs';

    protected $fillable = [
        'person_a_id',
        'person_b_id',
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

    public function personA(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_a_id');
    }

    public function personB(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_b_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'resolved_by');
    }
}
