<?php

namespace AHATechnocrats\OmicsLogic\Services\Import;

use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\Contact\Repositories\PersonRepository;
use AHATechnocrats\Lead\Repositories\SourceRepository;
use AHATechnocrats\OmicsLogic\Enums\LifecycleStage;
use AHATechnocrats\OmicsLogic\Services\OrganizationAssigneeResolver;
use AHATechnocrats\OmicsLogic\Services\OrganizationResolver;
use AHATechnocrats\User\Repositories\UserRepository;
use AHATechnocrats\WebForm\Models\WebForm;
use AHATechnocrats\WebForm\Models\WebFormSubmission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

/**
 * Import persons (and organizations) by name — same resolution path as
 * LeadImportProcessor, without creating pipeline leads.
 */
class PersonImportProcessor
{
    /**
     * Public identifier of the web form contact-only rows are attributed to.
     */
    protected const IMPORT_FORM_ID = 'csv-import-persons';

    /**
     * Memoized id of the contact-only import web form.
     */
    protected ?int $importWebFormId = null;

    public function __construct(
        protected PersonRepository $personRepository,
        protected SourceRepository $sourceRepository,
        protected UserRepository $userRepository,
        protected OrganizationResolver $organizationResolver,
        protected OrganizationAssigneeResolver $assigneeResolver,
    ) {}

    /**
     * Turn one name-based contact row into a person — resolving/creating the
     * organization, deduping by email, inheriting ownership. No lead is created.
     *
     * @param  array<string, mixed>  $row
     */
    public function process(array $row, ?int $defaultSourceId = null): ?Person
    {
        $row = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $row);

        $name = $this->clean($row['person_name'] ?? null) ?: $this->clean($row['name'] ?? null);
        $email = $this->clean($row['email'] ?? null);
        $phone = $this->clean($row['phone'] ?? null);

        if (! $name && ! $email && ! $phone) {
            return null;
        }

        $country = $this->clean($row['country'] ?? null);
        $jobTitle = $this->clean($row['job_title'] ?? null);
        $organizationName = $this->resolveOrganizationName($row);
        $rawEducation = $this->clean($row['education_level'] ?? null);
        $educationLevel = $this->normalizeEducation($rawEducation);
        $submittedAt = $this->parseTimestamp($this->clean($row['timestamp'] ?? null));
        $lifecycleStage = $this->resolveLifecycleStage($this->clean($row['lifecycle_stage'] ?? null));

        $organization = $this->organizationResolver->resolve(
            $organizationName,
            allowCreate: true,
            countryCode: $country,
            queueReview: true,
        );

        $existingPerson = $this->matchPersonByEmail($email);

        $ownerId = $this->resolveOwner($this->clean($row['owner'] ?? null))
            ?? $existingPerson?->user_id
            ?? $this->assigneeResolver->resolve($organization);

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

        if ($existingPerson) {
            $this->fillPersonBlanks(
                $existingPerson,
                $phone,
                $jobTitle,
                $organization,
                $educationLevel,
                $country,
                $lifecycleStage,
            );

            $person = $existingPerson->fresh();
        } else {
            $payload = $this->buildPersonPayload(
                $name,
                $email,
                $phone,
                $jobTitle,
                $organization,
                $ownerId,
                $sourceId,
                $country,
                $educationLevel,
                $lifecycleStage,
            );

            if (! $payload) {
                return null;
            }

            Event::dispatch('contacts.person.create.before');

            $person = $this->personRepository->create(array_merge($payload, [
                'entity_type' => 'persons',
            ]));

            Event::dispatch('contacts.person.create.after', $person);

            if ($submittedAt) {
                DB::table('persons')->where('id', $person->id)->update(['created_at' => $submittedAt]);
            }
        }

        $this->recordSubmission(
            $person,
            $name ?: $person->name,
            $email,
            $phone,
            $organization?->name ?: $organizationName,
            $country,
            $rawEducation,
            $this->clean($row['inquiry_details'] ?? null),
            $row['_raw_submission'] ?? null,
            $submittedAt,
        );

        return $person;
    }

    /**
     * Clients often put organization *names* in a column labeled organization_id.
     * Prefer organization_name; fall back to non-numeric organization_id values.
     *
     * @param  array<string, mixed>  $row
     */
    protected function resolveOrganizationName(array $row): ?string
    {
        $name = $this->clean($row['organization_name'] ?? null);

        if ($name) {
            return $name;
        }

        $organizationId = $this->clean($row['organization_id'] ?? null);

        if ($organizationId && ! ctype_digit($organizationId)) {
            return $organizationId;
        }

        return null;
    }

    protected function resolveLifecycleStage(?string $value): string
    {
        if ($value && LifecycleStage::tryFrom(strtolower($value))) {
            return strtolower($value);
        }

        return LifecycleStage::Customer->value;
    }

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

    protected function fillPersonBlanks(
        Person $person,
        ?string $phone,
        ?string $jobTitle,
        ?Organization $organization,
        ?string $educationLevel,
        ?string $country,
        string $lifecycleStage,
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

        $lifecycleValue = $person->lifecycle_stage;
        if (empty($person->lifecycle_stage)) {
            $lifecycleValue = $lifecycleStage;
            $changed = true;
        }

        if (! $changed) {
            return;
        }

        $payload = array_filter([
            'name' => $person->name,
            'user_id' => $person->user_id,
            'emails' => $person->emails ?: null,
            'contact_numbers' => $contactNumbers,
            'job_title' => $jobTitleValue,
            'organization_id' => $organizationId,
            'education_level' => $educationValue,
            'country_code' => $countryValue,
            'lifecycle_stage' => $lifecycleValue,
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
        string $lifecycleStage,
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
            'lifecycle_stage' => $lifecycleStage,
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @param  array<string, mixed>|null  $rawRow
     */
    protected function recordSubmission(
        Person $person,
        ?string $name,
        ?string $email,
        ?string $phone,
        ?string $organizationName,
        ?string $country,
        ?string $rawEducation,
        ?string $inquiry,
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

        $payload = ['persons' => $persons];

        if (! empty($rawRow)) {
            $payload['raw'] = $rawRow;
        }

        $timestamp = $submittedAt ?? now();

        WebFormSubmission::query()->create([
            'web_form_id' => $this->importWebFormId(),
            'person_id' => $person->id,
            'payload' => $payload,
            'spam_score' => 0,
            'status' => 'accepted',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    protected function importWebFormId(): int
    {
        if ($this->importWebFormId !== null) {
            return $this->importWebFormId;
        }

        $webForm = WebForm::query()->firstOrCreate(
            ['form_id' => self::IMPORT_FORM_ID],
            [
                'title' => 'CSV / Excel Contact Import',
                'description' => 'Persons and organizations imported from CSV / Excel without creating leads.',
                'submit_button_label' => 'Submit',
                'submit_success_action' => 'message',
                'submit_success_content' => 'Thank you',
                'create_lead' => false,
                'is_active' => true,
            ],
        );

        return $this->importWebFormId = $webForm->id;
    }

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
