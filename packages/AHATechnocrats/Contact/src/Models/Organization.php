<?php

namespace AHATechnocrats\Contact\Models;

use AHATechnocrats\Attribute\Traits\CustomAttribute;
use AHATechnocrats\Contact\Contracts\Organization as OrganizationContract;
use AHATechnocrats\User\Models\UserProxy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model implements OrganizationContract
{
    use CustomAttribute;

    protected $fillable = [
        'name',
        'user_id',
        'type',
        'country_code',
        'account_owner_id',
        'normalized_name',
        'website',
        'notes',
        'contacts_count',
        'engaged_count',
        'customers_count',
    ];

    /**
     * Get persons.
     *
     * @return HasMany
     */
    public function persons()
    {
        return $this->hasMany(PersonProxy::modelClass());
    }

    /**
     * Get the user that owns the lead.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * Account owner for institutional sales.
     */
    public function accountOwner()
    {
        return $this->belongsTo(UserProxy::modelClass(), 'account_owner_id');
    }
}
