<?php

namespace AHATechnocrats\OmicsLogic\Models;

use AHATechnocrats\User\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'omics_audit_logs';

    protected $fillable = [
        'actor_type',
        'actor_id',
        'action',
        'event',
        'description',
        'entity_type',
        'entity_id',
        'before',
        'after',
        'route',
        'ip_address',
        'user_agent',
        'is_reversible',
        'reversed_at',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'is_reversible' => 'boolean',
        'reversed_at' => 'datetime',
    ];

    /**
     * Resolve the acting user, when the actor was an authenticated user.
     */
    public function actor(): ?User
    {
        if ($this->actor_type !== 'user' || ! $this->actor_id) {
            return null;
        }

        return User::query()->find($this->actor_id);
    }
}
