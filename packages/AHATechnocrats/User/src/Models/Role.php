<?php

namespace AHATechnocrats\User\Models;

use AHATechnocrats\User\Contracts\Role as RoleContract;
use Illuminate\Database\Eloquent\Model;

class Role extends Model implements RoleContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'permission_type',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * Get the users.
     */
    public function users()
    {
        return $this->hasMany(UserProxy::modelClass());
    }
}
