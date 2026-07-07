<?php

namespace AHATechnocrats\OmicsLogic\Services\Portal;

use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\Lead\Models\Lead;
use AHATechnocrats\Lead\Repositories\SourceRepository;
use AHATechnocrats\OmicsLogic\Models\Connector;
use AHATechnocrats\OmicsLogic\Services\Import\LeadImportProcessor;

class PortalLeadSyncService
{
    public function __construct(
        protected PortalApiClient $client,
        protected LeadImportProcessor $leadImportProcessor,
        protected SourceRepository $sourceRepository,
    ) {}

    /**
     * Pull portal records and import only contacts that are not already in the CRM.
     *
     * @return array{rows_total: int, rows_new: int, rows_merged: int, rows_review: int, rows_failed: int}
     */
    public function sync(Connector $connector): array
    {
        $config = $connector->config ?? [];

        if (empty($config['api_url'])) {
            throw new \RuntimeException('Portal API URL is not configured.');
        }

        if ($connector->status === 'disabled') {
            throw new \RuntimeException('Portal connector is disabled. Enable it in Configure first.');
        }

        $leads = $this->client->fetchLeads(
            $config['api_url'],
            $config['api_token'] ?? null,
            $connector->last_sync_at,
            $config['leads_path'] ?? null,
        );

        $sourceId = $this->resolvePortalSourceId();

        $stats = [
            'rows_total' => count($leads),
            'rows_new' => 0,
            'rows_merged' => 0,
            'rows_review' => 0,
            'rows_failed' => 0,
        ];

        foreach ($leads as $record) {
            try {
                if ($this->isAlreadyKnown($record)) {
                    continue;
                }

                $lead = $this->leadImportProcessor->process(
                    $this->mapRecordToImportRow($record),
                    $sourceId,
                );

                if (! $lead instanceof Lead) {
                    $stats['rows_failed']++;

                    continue;
                }

                $this->applyPortalFields($lead->person, $record);
                $stats['rows_new']++;
            } catch (\Throwable $exception) {
                $stats['rows_failed']++;
                report($exception);
            }
        }

        return $stats;
    }

    protected function isAlreadyKnown(array $record): bool
    {
        $portalUserId = $this->portalUserId($record);

        if ($portalUserId && Person::query()
            ->whereNull('merged_into_id')
            ->where('portal_user_id', $portalUserId)
            ->exists()) {
            return true;
        }

        $email = $this->normalizeEmail($record['email'] ?? $record['email_address'] ?? null);

        if ($email && Person::query()
            ->whereNull('merged_into_id')
            ->where('normalized_email', $email)
            ->exists()) {
            return true;
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapRecordToImportRow(array $record): array
    {
        $name = $record['name']
            ?? $record['full_name']
            ?? trim(($record['first_name'] ?? '').' '.($record['last_name'] ?? ''));

        $name = is_string($name) ? trim($name) : '';

        return array_filter([
            'title' => $record['title'] ?? ('Portal — '.($name ?: 'Lead')),
            'person_name' => $name ?: null,
            'email' => $record['email'] ?? $record['email_address'] ?? null,
            'phone' => $record['phone'] ?? $record['phone_number'] ?? null,
            'country' => $record['country'] ?? $record['country_code'] ?? null,
            'organization_name' => $record['organization'] ?? $record['organization_name'] ?? $record['company'] ?? null,
            'education_level' => $record['education_level'] ?? $record['education'] ?? null,
            'job_title' => $record['job_title'] ?? null,
            'product' => $record['program'] ?? $record['product'] ?? $record['program_interest'] ?? null,
            'timestamp' => $record['created_at'] ?? $record['registered_at'] ?? null,
            'source' => 'OmicsLogic Portal',
            'lead_type' => 'New',
            'description' => $record['description'] ?? null,
            '_raw_submission' => $record,
        ], fn ($value) => $value !== null && $value !== '');
    }

    protected function applyPortalFields(?Person $person, array $record): void
    {
        if (! $person) {
            return;
        }

        $updates = [];

        if ($portalUserId = $this->portalUserId($record)) {
            $updates['portal_user_id'] = $portalUserId;
        }

        if (array_key_exists('engagement_lessons', $record) || array_key_exists('lessons_completed', $record)) {
            $updates['engagement_lessons'] = (int) ($record['engagement_lessons'] ?? $record['lessons_completed'] ?? 0);
        }

        if (array_key_exists('is_student', $record)) {
            $updates['is_student'] = (bool) $record['is_student'];
        }

        if ($updates !== []) {
            $person->update($updates);
        }
    }

    protected function portalUserId(array $record): ?string
    {
        $id = $record['portal_user_id']
            ?? $record['user_id']
            ?? $record['id']
            ?? null;

        if ($id === null || $id === '') {
            return null;
        }

        return (string) $id;
    }

    protected function normalizeEmail(?string $email): ?string
    {
        if (! $email) {
            return null;
        }

        $normalized = strtolower(trim($email));

        return $normalized !== '' ? $normalized : null;
    }

    protected function resolvePortalSourceId(): ?int
    {
        $source = $this->sourceRepository->findOneByField('name', 'OmicsLogic Portal')
            ?: $this->sourceRepository->create(['name' => 'OmicsLogic Portal']);

        return $source?->id;
    }
}
