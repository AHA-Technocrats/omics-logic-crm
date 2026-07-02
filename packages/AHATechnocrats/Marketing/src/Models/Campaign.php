<?php

namespace AHATechnocrats\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use AHATechnocrats\EmailTemplate\Models\EmailTemplateProxy;
use AHATechnocrats\Marketing\Contracts\Campaign as CampaignContract;

class Campaign extends Model implements CampaignContract
{
    /**
     * Define the table for the model.
     *
     * @var string
     */
    protected $table = 'marketing_campaigns';

    /**
     * The attributes that are fillable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'subject',
        'status',
        'marketing_template_id',
        'marketing_event_id',
        'spooling',
    ];

    /**
     * Get the email template
     */
    public function email_template()
    {
        return $this->belongsTo(EmailTemplateProxy::modelClass(), 'marketing_template_id');
    }

    /**
     * Get the event
     */
    public function event()
    {
        return $this->belongsTo(EventProxy::modelClass(), 'marketing_event_id');
    }
}
