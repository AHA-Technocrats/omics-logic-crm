<?php

namespace AHATechnocrats\Contact\Models;

use AHATechnocrats\Activity\Models\ActivityProxy;
use AHATechnocrats\Activity\Traits\LogsActivity;
use AHATechnocrats\Attribute\Traits\CustomAttribute;
use AHATechnocrats\Contact\Contracts\Person as PersonContract;
use AHATechnocrats\Contact\Database\Factories\PersonFactory;
use AHATechnocrats\Lead\Models\LeadProxy;
use AHATechnocrats\Lead\Models\SourceProxy;
use AHATechnocrats\Product\Models\ProductProxy;
use AHATechnocrats\Tag\Models\TagProxy;
use AHATechnocrats\User\Models\UserProxy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model implements PersonContract
{
    use CustomAttribute, HasFactory, LogsActivity;

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'persons';

    /**
     * Eager loading.
     *
     * @var string
     */
    protected $with = 'organization';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'emails',
        'contact_numbers',
        'job_title',
        'user_id',
        'organization_id',
        'unique_id',
        'lifecycle_stage',
        'is_student',
        'converted_at',
        'lead_score',
        'country_code',
        'education_level',
        'inquiry_details',
        'primary_source_id',
        'portal_user_id',
        'primary_product_id',
        'sales_stage',
        'next_action',
        'next_action_due',
        'last_contacted_at',
        'last_activity_at',
        'engagement_lessons',
        'is_opted_out',
        'normalized_email',
        'normalized_phone',
        'spam_score',
        'spam_status',
        'merged_into_id',
    ];

    protected $casts = [
        'emails' => 'array',
        'contact_numbers' => 'array',
        'is_student' => 'boolean',
        'converted_at' => 'datetime',
        'last_contacted_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'next_action_due' => 'date',
        'is_opted_out' => 'boolean',
    ];

    /**
     * Get the user that owns the lead.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * Get the organization that owns the person.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(OrganizationProxy::modelClass());
    }

    public function primarySource(): BelongsTo
    {
        return $this->belongsTo(SourceProxy::modelClass(), 'primary_source_id');
    }

    public function primaryProduct(): BelongsTo
    {
        return $this->belongsTo(ProductProxy::modelClass(), 'primary_product_id');
    }

    /**
     * Get the activities.
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(ActivityProxy::modelClass(), 'person_activities');
    }

    /**
     * The tags that belong to the person.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TagProxy::modelClass(), 'person_tags');
    }

    /**
     * Get the leads for the person.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(LeadProxy::modelClass(), 'person_id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): PersonFactory
    {
        return PersonFactory::new();
    }
}
