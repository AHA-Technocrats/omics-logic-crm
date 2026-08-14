<?php

namespace AHATechnocrats\Automation\Helpers\Entity;

use AHATechnocrats\Admin\Notifications\Common;
use AHATechnocrats\Attribute\Repositories\AttributeRepository;
use AHATechnocrats\Automation\Contracts\Workflow;
use AHATechnocrats\Automation\Repositories\WebhookRepository;
use AHATechnocrats\Automation\Services\WebhookService;
use AHATechnocrats\Contact\Contracts\Person as PersonContract;
use AHATechnocrats\Contact\Repositories\PersonRepository;
use AHATechnocrats\Core\Services\SafeMailDispatcher;
use AHATechnocrats\EmailTemplate\Repositories\EmailTemplateRepository;
use AHATechnocrats\Lead\Repositories\LeadRepository;

class Person extends AbstractEntity
{
    /**
     * CRM fields stored directly on persons rather than in the attributes table.
     *
     * @var array<string, string>
     */
    protected const CRM_PLACEHOLDERS = [
        'job_title' => 'Job Title',
        'unique_id' => 'Unique ID',
        'is_student' => 'Is Student',
        'converted_at' => 'Converted At',
        'lead_score' => 'Lead Score',
        'product_interest_points' => 'Product Interest Points',
        'email_domain_points' => 'Email Domain Points',
        'country_points' => 'Country Points',
        'profile_points' => 'Profile Points',
        'score_band' => 'Score Band',
        'country_code' => 'Country',
        'education_level' => 'Level of Education',
        'inquiry_details' => 'Inquiry Details',
        'primary_source_id' => 'Primary Source',
        'portal_user_id' => 'Portal User ID',
        'primary_product_id' => 'Primary Campaign',
        'program_interest' => 'Interested in Program',
        'sales_stage' => 'Sales Stage',
        'next_action' => 'Next Action',
        'next_action_due' => 'Next Action Due',
        'last_contacted_at' => 'Last Contacted At',
        'last_activity_at' => 'Last Activity At',
        'engagement_lessons' => 'Engagement Lessons',
        'is_opted_out' => 'Is Opted Out',
        'spam_score' => 'Spam Score',
        'spam_status' => 'Spam Status',
    ];

    /**
     * Define the entity type.
     */
    protected string $entityType = 'persons';

    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected EmailTemplateRepository $emailTemplateRepository,
        protected LeadRepository $leadRepository,
        protected PersonRepository $personRepository,
        protected WebhookRepository $webhookRepository,
        protected WebhookService $webhookService
    ) {}

    /**
     * Listing of the entities.
     */
    public function getEntity(mixed $entity): mixed
    {
        if (! $entity instanceof PersonContract) {
            $entity = $this->personRepository->find($entity);
        }

        return $entity;
    }

    public function getEmailTemplatePlaceholders(array $entity): array
    {
        $placeholders = parent::getEmailTemplatePlaceholders($entity);
        $existing = array_column($placeholders['menu'], 'value');

        foreach (self::CRM_PLACEHOLDERS as $code => $label) {
            $token = "{%persons.{$code}%}";

            if (! in_array($token, $existing, true)) {
                $placeholders['menu'][] = [
                    'text' => $label,
                    'value' => $token,
                ];
            }
        }

        return $placeholders;
    }

    public function replacePlaceholders(mixed $entity, string $content): string
    {
        $content = parent::replacePlaceholders($entity, $content);

        foreach (self::CRM_PLACEHOLDERS as $code => $label) {
            $value = match ($code) {
                'primary_source_id' => $entity->primarySource?->name,
                'primary_product_id', 'program_interest' => $entity->primaryProduct?->name,
                'is_student', 'is_opted_out' => $entity->{$code}
                    ? trans('admin::app.common.yes')
                    : trans('admin::app.common.no'),
                'converted_at', 'last_contacted_at', 'last_activity_at' => $entity->{$code}
                    ? $entity->{$code}->format('D M d, Y H:i A')
                    : null,
                'next_action_due' => $entity->{$code}
                    ? $entity->{$code}->format('D M d, Y')
                    : null,
                default => $entity->{$code},
            };

            $content = strtr($content, [
                "{%persons.{$code}%}" => (string) ($value ?? ''),
                "{% persons.{$code} %}" => (string) ($value ?? ''),
            ]);
        }

        if (str_contains($content, '{%leads.') || str_contains($content, '{% leads.')) {
            $lead = $entity->leads()->latest('id')->first();

            if ($lead) {
                $content = app(Lead::class)->replacePlaceholders($lead, $content);
            }
        }

        return $content;
    }

    /**
     * Returns workflow actions.
     */
    public function getActions(): array
    {
        $emailTemplates = $this->emailTemplateRepository->all(['id', 'name']);

        $webhooksOptions = $this->webhookRepository->all(['id', 'name']);

        return [
            [
                'id' => 'update_person',
                'name' => trans('admin::app.settings.workflows.helpers.update-person'),
                'attributes' => $this->getAttributes('persons'),
            ], [
                'id' => 'update_related_leads',
                'name' => trans('admin::app.settings.workflows.helpers.update-related-leads'),
                'attributes' => $this->getAttributes('leads'),
            ], [
                'id' => 'send_email_to_person',
                'name' => trans('admin::app.settings.workflows.helpers.send-email-to-person'),
                'options' => $emailTemplates,
            ], [
                'id' => 'trigger_webhook',
                'name' => trans('admin::app.settings.workflows.helpers.add-webhook'),
                'options' => $webhooksOptions,
            ],
        ];
    }

    /**
     * Execute workflow actions.
     */
    public function executeActions(mixed $workflow, mixed $person): void
    {
        foreach ($workflow->actions as $action) {
            switch ($action['id']) {
                case 'update_person':
                    $this->personRepository->update([
                        'entity_type' => 'persons',
                        $action['attribute'] => $action['value'],
                    ], $person->id);

                    break;

                case 'update_related_leads':
                    $leads = $this->leadRepository->findByField('person_id', $person->id);

                    foreach ($leads as $lead) {
                        $this->leadRepository->update(
                            [
                                'entity_type' => 'leads',
                                $action['attribute'] => $action['value'],
                            ],
                            $lead->id,
                            [$action['attribute']]
                        );
                    }

                    break;

                case 'send_email_to_person':
                    $emailTemplate = $this->emailTemplateRepository->find($action['value']);

                    if (! $emailTemplate) {
                        break;
                    }

                    app(SafeMailDispatcher::class)->dispatch(new Common([
                        'to' => data_get($person->emails, '*.value'),
                        'subject' => $this->replacePlaceholders($person, $emailTemplate->subject),
                        'body' => $this->replacePlaceholders($person, $emailTemplate->content),
                    ]));

                    break;

                case 'trigger_webhook':
                    try {
                        $this->triggerWebhook($action['value'], $person);
                    } catch (\Exception $e) {
                        report($e);
                    }

                    break;
            }
        }
    }
}
