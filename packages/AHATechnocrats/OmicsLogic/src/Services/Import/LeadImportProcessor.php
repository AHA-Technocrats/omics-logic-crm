<?php

namespace AHATechnocrats\OmicsLogic\Services\Import;

use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\Contact\Repositories\PersonRepository;
use AHATechnocrats\Lead\Models\Lead;
use AHATechnocrats\Lead\Repositories\LeadRepository;
use AHATechnocrats\Lead\Repositories\PipelineRepository;
use AHATechnocrats\Lead\Repositories\SourceRepository;
use AHATechnocrats\Lead\Repositories\TypeRepository;
use AHATechnocrats\OmicsLogic\Enums\LifecycleStage;
use AHATechnocrats\OmicsLogic\Services\OrganizationAssigneeResolver;
use AHATechnocrats\OmicsLogic\Services\OrganizationResolver;
use AHATechnocrats\Product\Repositories\ProductRepository;
use AHATechnocrats\User\Repositories\UserRepository;
use AHATechnocrats\WebForm\Models\WebForm;
use AHATechnocrats\WebForm\Models\WebFormSubmission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class LeadImportProcessor
{
    /**
     * Lead type applied when the contact already exists in the CRM.
     */
    protected const TYPE_EXISTING = 'Existing';

    /**
     * Lead type applied when the contact is brand new.
     */
    protected const TYPE_NEW = 'New';

    /**
     * Public identifier of the web form imported rows are attributed to.
     */
    protected const IMPORT_FORM_ID = 'csv-import';

    /**
     * Memoized id of the CSV/Excel import web form.
     */
    protected ?int $importWebFormId = null;

    public function __construct(
        protected LeadRepository $leadRepository,
        protected SourceRepository $sourceRepository,
        protected TypeRepository $typeRepository,
        protected PipelineRepository $pipelineRepository,
        protected ProductRepository $productRepository,
        protected UserRepository $userRepository,
        protected PersonRepository $personRepository,
        protected OrganizationResolver $organizationResolver,
        protected OrganizationAssigneeResolver $assigneeResolver,
    ) {}

    /**
     * Turn one name-based lead row into a lead — resolving/creating the
     * organization and contact, deduping by email, inheriting ownership, and
     * preserving the original submission as a web form submission.
     */
    public function process(array $row, ?int $defaultSourceId = null): ?Lead
    {
        $row = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $row);

        $title = $this->clean($row['title'] ?? null) ?: $this->clean($row['person_name'] ?? null);

        if (! $title) {
            return null;
        }

        $name = $this->clean($row['person_name'] ?? null);
        $email = $this->clean($row['email'] ?? null);
        $phone = $this->clean($row['phone'] ?? null);
        $country = $this->clean($row['country'] ?? null);
        $jobTitle = $this->clean($row['job_title'] ?? null);
        $organizationName = $this->clean($row['organization_name'] ?? null);
        $rawEducation = $this->clean($row['education_level'] ?? null);
        $educationLevel = $this->normalizeEducation($rawEducation);
        $submittedAt = $this->parseTimestamp($this->clean($row['timestamp'] ?? null));

        $organization = $this->organizationResolver->resolve(
            $organizationName,
            allowCreate: true,
            countryCode: $country,
            queueReview: true,
        );

        /**
         * Dedup strictly on the (normalized) email address.
         */
        $existingPerson = $this->matchPersonByEmail($email);

        /**
         * Ownership precedence: an explicit owner column wins, otherwise the
         * existing contact's owner, then the organization's owner, and finally
         * the super admin (handled by the assignee resolver).
         */
        $ownerId = $this->resolveOwner($this->clean($row['owner'] ?? null))
            ?? $existingPerson?->user_id
            ?? $this->assigneeResolver->resolve($organization);

        /**
         * A freshly created organization inherits the resolved lead owner.
         */
        if (
            $organization
            && $organization->wasRecentlyCreated
            && empty($organization->account_owner_id)
            && $ownerId
        ) {
            $organization->account_owner_id = $ownerId;
            $organization->save();
        }

        $sourceId = $this->resolveSource($this->clean($row['source'] ?? null), $defaultSourceId);

        $typeName = $this->clean($row['lead_type'] ?? null)
            ?: ($existingPerson ? self::TYPE_EXISTING : self::TYPE_NEW);
        $typeId = $this->resolveType($typeName);

        [$pipelineId, $stageId] = $this->resolvePipelineStage(
            $this->clean($row['pipeline'] ?? null),
            $this->clean($row['stage'] ?? null),
        );

        $data = array_filter([
            'entity_type' => 'leads',
            'title' => $title,
            'description' => $this->clean($row['description'] ?? null),
            'lead_value' => $this->clean($row['lead_value'] ?? null) ?? 0,
            'status' => isset($row['status']) && $row['status'] !== '' ? (int) $row['status'] : 1,
            'user_id' => $ownerId,
            'lead_source_id' => $sourceId,
            'lead_type_id' => $typeId,
            'lead_pipeline_id' => $pipelineId,
            'lead_pipeline_stage_id' => $stageId,
            'expected_close_date' => $this->clean($row['expected_close_date'] ?? null),
        ], fn ($value) => $value !== null);

        if ($products = $this->resolveProducts($this->clean($row['product'] ?? null))) {
            $data['products'] = $products;
        }

        if ($existingPerson) {
            $this->fillPersonBlanks($existingPerson, $phone, $jobTitle, $organization, $educationLevel, $country);

            $data['person_id'] = $existingPerson->id;
        } elseif ($person = $this->buildPersonPayload($name, $email, $phone, $jobTitle, $organization, $ownerId, $sourceId, $country, $educationLevel)) {
            $data['person'] = $person;
        }

        Event::dispatch('lead.create.before');

        $lead = $this->leadRepository->create($data);

        Event::dispatch('lead.create.after', $lead);

        /**
         * Back-date the lead (and a brand-new contact) to the submission time.
         */
        if ($submittedAt) {
            DB::table('leads')->where('id', $lead->id)->update(['created_at' => $submittedAt]);

            if (! $existingPerson && $lead->person_id) {
                DB::table('persons')->where('id', $lead->person_id)->update(['created_at' => $submittedAt]);
            }
        }

        /**
         * Preserve the raw submission so it shows on the lead as web form data.
         */
        if ($lead->person_id) {
            $this->recordSubmission(
                $lead,
                $name,
                $email,
                $phone,
                $organization?->name ?: $organizationName,
                $country,
                $rawEducation,
                $this->clean($row['inquiry_details'] ?? null),
                $title,
                $row['_raw_submission'] ?? null,
                $submittedAt,
            );
        }

        return $lead;
    }

    /**
     * Match an incoming contact strictly by normalized email address.
     */
    protected function matchPersonByEmail(?string $email): ?Person
    {
        $normalized = $email ? strtolower(trim($email)) : null;

        if (! $normalized) {
            return null;
        }

        return Person::query()
            ->whereNull('merged_into_id')
            ->where('normalized_email', $normalized)
            ->first();
    }

    /**
     * Fill only the empty profile fields of an existing contact — never
     * overwrite data the contact already has.
     */
    protected function fillPersonBlanks(
        Person $person,
        ?string $phone,
        ?string $jobTitle,
        ?Organization $organization,
        ?string $educationLevel,
        ?string $country,
    ): void {
        $changed = false;

        $contactNumbers = $person->contact_numbers ?: null;
        if (empty($person->contact_numbers) && $phone) {
            $contactNumbers = [['value' => $phone, 'label' => 'work']];
            $changed = true;
        }

        $jobTitleValue = $person->job_title;
        if (empty($person->job_title) && $jobTitle) {
            $jobTitleValue = $jobTitle;
            $changed = true;
        }

        $organizationId = $person->organization_id;
        if (empty($person->organization_id) && $organization) {
            $organizationId = $organization->id;
            $changed = true;
        }

        $educationValue = $person->education_level;
        if (empty($person->education_level) && $educationLevel) {
            $educationValue = $educationLevel;
            $changed = true;
        }

        $countryValue = $person->country_code;
        if (empty($person->country_code) && $country) {
            $countryValue = $country;
            $changed = true;
        }

        if (! $changed) {
            return;
        }

        /**
         * Preserve existing values (name/owner/email keep unique_id + owner
         * intact) and only send non-empty keys — passing an empty
         * organization_id would otherwise clear the derived country_code.
         */
        $payload = array_filter([
            'name' => $person->name,
            'user_id' => $person->user_id,
            'emails' => $person->emails ?: null,
            'contact_numbers' => $contactNumbers,
            'job_title' => $jobTitleValue,
            'organization_id' => $organizationId,
            'education_level' => $educationValue,
            'country_code' => $countryValue,
        ], fn ($value) => $value !== null && $value !== '' && $value !== []);

        $this->personRepository->update($payload, $person->id);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function buildPersonPayload(
        ?string $name,
        ?string $email,
        ?string $phone,
        ?string $jobTitle,
        ?Organization $organization,
        ?int $ownerId,
        ?int $sourceId,
        ?string $country,
        ?string $educationLevel,
    ): ?array {
        if (! $name && ! $email && ! $phone) {
            return null;
        }

        return array_filter([
            'name' => $name ?: ($email ?: $phone),
            'emails' => $email ? [['value' => $email, 'label' => 'work']] : null,
            'contact_numbers' => $phone ? [['value' => $phone, 'label' => 'work']] : null,
            'job_title' => $jobTitle,
            'organization_id' => $organization?->id,
            'country_code' => $organization?->country_code ?: $country,
            'education_level' => $educationLevel,
            'primary_source_id' => $sourceId,
            'user_id' => $ownerId,
            'lifecycle_stage' => LifecycleStage::Lead->value,
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * Store the original row as a web form submission so the existing lead-view
     * panel surfaces the raw submitted values.
     */
    protected function recordSubmission(
        Lead $lead,
        ?string $name,
        ?string $email,
        ?string $phone,
        ?string $organizationName,
        ?string $country,
        ?string $rawEducation,
        ?string $inquiry,
        ?string $title,
        ?array $rawRow,
        ?Carbon $submittedAt,
    ): void {
        $persons = array_filter([
            'name' => $name,
            'emails' => $email ? [['value' => $email, 'label' => 'work']] : null,
            'contact_numbers' => $phone ? [['value' => $phone, 'label' => 'work']] : null,
            'organization_name' => $organizationName,
            'country_code' => $country,
            'education_level' => $rawEducation,
            'inquiry_details' => $inquiry,
        ], fn ($value) => $value !== null && $value !== '');

        $leads = array_filter([
            'title' => $title,
        ], fn ($value) => $value !== null && $value !== '');

        $payload = ['persons' => $persons, 'leads' => $leads];

        if (! empty($rawRow)) {
            $payload['raw'] = $rawRow;
        }

        $timestamp = $submittedAt ?? now();

        WebFormSubmission::query()->create([
            'web_form_id' => $this->importWebFormId(),
            'person_id' => $lead->person_id,
            'payload' => $payload,
            'spam_score' => 0,
            'status' => 'accepted',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    /**
     * Find (or create) the dedicated web form imported rows are attributed to.
     */
    protected function importWebFormId(): int
    {
        if ($this->importWebFormId !== null) {
            return $this->importWebFormId;
        }

        $webForm = WebForm::query()->firstOrCreate(
            ['form_id' => self::IMPORT_FORM_ID],
            [
                'title' => 'CSV / Excel Import',
                'description' => 'Leads imported from CSV / Excel files.',
                'submit_button_label' => 'Submit',
                'submit_success_action' => 'message',
                'submit_success_content' => 'Thank you',
                'create_lead' => true,
                'is_active' => true,
            ],
        );

        return $this->importWebFormId = $webForm->id;
    }

    /**
     * Normalize free-text education into a fixed category.
     */
    protected function normalizeEducation(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $needle = strtolower($value);

        return match (true) {
            str_contains($needle, 'phd'),
            str_contains($needle, 'ph.d'),
            str_contains($needle, 'doctor') => 'Doctorate',

            str_contains($needle, 'undergrad'),
            str_contains($needle, 'bachelor'),
            str_contains($needle, 'b.sc'),
            str_contains($needle, 'b.a') => 'Bachelors',

            str_contains($needle, 'master'),
            str_contains($needle, 'graduate'),
            str_contains($needle, 'm.sc'),
            str_contains($needle, 'msc'),
            str_contains($needle, 'postgrad') => 'Masters',

            str_contains($needle, 'faculty'),
            str_contains($needle, 'professor'),
            str_contains($needle, 'lecturer') => 'Faculty',

            default => 'Other',
        };
    }

    /**
     * Parse a submission timestamp using common (Google Forms) formats.
     */
    protected function parseTimestamp(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        foreach (['n/j/Y G:i:s', 'n/j/Y H:i:s', 'm/d/Y H:i:s', 'n/j/Y g:i:s A', 'Y-m-d H:i:s', 'd/m/Y H:i:s'] as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $value);

                if ($parsed !== false) {
                    return $parsed;
                }
            } catch (\Throwable $exception) {
                // Try the next format.
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    protected function resolveOwner(?string $value): ?int
    {
        if (! $value) {
            return null;
        }

        $user = $this->userRepository->findOneByField('email', $value)
            ?: $this->userRepository->findOneByField('name', $value);

        return $user?->id;
    }

    protected function resolveSource(?string $name, ?int $defaultSourceId): ?int
    {
        if (! $name) {
            return $defaultSourceId;
        }

        $source = $this->sourceRepository->findOneByField('name', $name)
            ?: $this->sourceRepository->create(['name' => $name]);

        return $source?->id ?? $defaultSourceId;
    }

    protected function resolveType(?string $name): ?int
    {
        if (! $name) {
            return $this->typeRepository->first()?->id;
        }

        $type = $this->typeRepository->findOneByField('name', $name)
            ?: $this->typeRepository->create(['name' => $name]);

        return $type?->id;
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    protected function resolvePipelineStage(?string $pipelineName, ?string $stageName): array
    {
        $pipeline = $pipelineName
            ? $this->pipelineRepository->findOneByField('name', $pipelineName)
            : null;

        $pipeline = $pipeline ?: $this->pipelineRepository->getDefaultPipeline();

        if (! $pipeline) {
            return [null, null];
        }

        $stage = null;

        if ($stageName) {
            $stage = $pipeline->stages()->where('name', $stageName)->first()
                ?: $pipeline->stages()->where('code', $stageName)->first();
        }

        $stage = $stage ?: $pipeline->stages()->first();

        return [$pipeline->id, $stage?->id];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function resolveProducts(?string $name): array
    {
        if (! $name) {
            return [];
        }

        $product = $this->productRepository->findOneByField('name', $name);

        if (! $product) {
            $aliasProductId = DB::table('omics_product_aliases')
                ->where('alias_name', $name)
                ->value('product_id');

            $product = $aliasProductId ? $this->productRepository->find($aliasProductId) : null;
        }

        if (! $product) {
            return [];
        }

        $price = (float) ($product->price ?? 0);

        return [[
            'product_id' => $product->id,
            'price' => $price,
            'quantity' => 1,
        ]];
    }

    protected function clean(mixed $value): ?string
    {
        if ($value === null || is_array($value)) {
            return null;
        }

        if (is_object($value)) {
            if (! method_exists($value, '__toString')) {
                return null;
            }

            $value = (string) $value;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
