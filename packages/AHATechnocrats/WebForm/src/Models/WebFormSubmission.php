<?php

namespace AHATechnocrats\WebForm\Models;

use AHATechnocrats\Contact\Models\PersonProxy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebFormSubmission extends Model
{
    protected $fillable = [
        'web_form_id',
        'person_id',
        'payload',
        'ip_address',
        'user_agent',
        'spam_score',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function webForm(): BelongsTo
    {
        return $this->belongsTo(WebFormProxy::modelClass(), 'web_form_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(PersonProxy::modelClass(), 'person_id');
    }
}
