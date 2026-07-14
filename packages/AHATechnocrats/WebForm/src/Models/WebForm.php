<?php

namespace AHATechnocrats\WebForm\Models;

use AHATechnocrats\EmailTemplate\Models\EmailTemplateProxy;
use AHATechnocrats\WebForm\Contracts\WebForm as WebFormContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebForm extends Model implements WebFormContract
{
    public const DEFAULT_THANK_YOU_CONTENT = '<h2>Your response has been recorded.</h2><p>Thank you for submitting the form.</p>';

    protected $fillable = [
        'form_id',
        'short_url_key',
        'title',
        'description',
        'submit_button_label',
        'submit_success_action',
        'submit_success_content',
        'thank_you_content',
        'create_lead',
        'is_active',
        'send_submitter_email',
        'email_template_id',
        'product_id',
        'campaign_scope',
        'turnstile_enabled',
        'honeypot_enabled',
        'min_submit_seconds',
        'rate_limit_per_ip',
        'rate_limit_per_email',
        'block_disposable',
        'organization_field',
        'allow_org_create',
        'program_field',
        'program_options',
        'field_order',
        'background_color',
        'form_background_color',
        'form_title_color',
        'form_submit_button_color',
        'attribute_label_color',
    ];

    protected $casts = [
        'create_lead' => 'boolean',
        'is_active' => 'boolean',
        'send_submitter_email' => 'boolean',
        'turnstile_enabled' => 'boolean',
        'honeypot_enabled' => 'boolean',
        'block_disposable' => 'boolean',
        'allow_org_create' => 'boolean',
        'field_order' => 'array',
        'program_options' => 'array',
    ];

    /**
     * The attributes that belong to the activity.
     */
    public function attributes()
    {
        return $this->hasMany(WebFormAttributeProxy::modelClass())->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(WebFormSubmission::class, 'web_form_id');
    }

    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplateProxy::modelClass(), 'email_template_id');
    }

    public function resolvedThankYouContent(): string
    {
        if (filled($this->thank_you_content)) {
            return $this->thank_you_content;
        }

        if (
            ($this->submit_success_action ?? 'message') === 'message'
            && filled($this->submit_success_content)
        ) {
            return '<p>'.e($this->submit_success_content).'</p>';
        }

        return self::DEFAULT_THANK_YOU_CONTENT;
    }
}
