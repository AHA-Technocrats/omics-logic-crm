<?php

namespace App\Firebase\Services;

use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\Contact\Repositories\PersonRepository;
use AHATechnocrats\Lead\Models\Lead;
use AHATechnocrats\Lead\Repositories\LeadRepository;
use AHATechnocrats\Lead\Repositories\PipelineRepository;
use AHATechnocrats\Lead\Repositories\SourceRepository;
use AHATechnocrats\Lead\Repositories\TypeRepository;
use AHATechnocrats\OmicsLogic\Enums\LifecycleStage;
use AHATechnocrats\OmicsLogic\Models\OrganizationMergeReviewPair;
use AHATechnocrats\OmicsLogic\Services\WebFormSubmissionMapper;
use AHATechnocrats\WebForm\Models\WebFormSubmission;
use AHATechnocrats\WebForm\Repositories\WebFormRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class FormSyncService
{
    public function __construct(
        protected FormService $formService,
        protected FirestoreFormMapper $formMapper,
        protected WebFormSubmissionMapper $submissionMapper,
        protected WebFormRepository $webFormRepository,
        protected PersonRepository $personRepository,
        protected LeadRepository $leadRepository,
        protected PipelineRepository $pipelineRepository,
        protected SourceRepository $sourceRepository,
        protected TypeRepository $typeRepository,
    ) {}

    /**
     * @return array{synced: int, skipped: int, failed: int}
     */
    public function sync(?int $webFormId = null, ?int $batchSize = null, ?Carbon $since = null): array
    {
        if (! config('firebase.sync.enabled', true)) {
            throw new \RuntimeException('Firebase form sync is disabled.');
        }

        $webFormId = $webFormId ?: (int) config('firebase.sync.default_web_form_id');

        if ($webFormId <= 0) {
            throw new \RuntimeException('No web form is configured for Firebase sync. Open Connectors → OmicsLogic Portal and select a CRM web form mapping.');
        }

        $webForm = $this->webFormRepository->find($webFormId);

        if (! $webForm) {
            throw new \RuntimeException("Configured web form #{$webFormId} was not found. Open Connectors → OmicsLogic Portal and select a valid CRM web form mapping.");
        }

        $batchSize = $batchSize ?: (int) config('firebase.sync.batch_size', 50);
        $cursor = null;
        $stats = ['synced' => 0, 'skipped' => 0, 'failed' => 0];
        $fetched = 0;

        do {
            $result = $this->formService->getFormsSince($since, $batchSize, $cursor);

            if (isset($result['success']) && $result['success'] === false) {
                throw new \RuntimeException($result['message'] ?? 'Unable to fetch Firestore forms.');
            }

            foreach ($result['items'] as $document) {
                $fetched++;
                $docId = (string) ($document['id'] ?? '');

                if ($docId === '' || $this->isAlreadySynced($docId)) {
                    $stats['skipped']++;

                    continue;
                }

                try {
                    $this->importFormDocument($document, $webForm);
                    $this->markSynced($docId);
                    $stats['synced']++;
                } catch (\Throwable $exception) {
                    report($exception);
                    $stats['failed']++;
                }
            }

            $cursor = $result['meta']['next_cursor'] ?? null;
        } while (! empty($result['meta']['has_more']) && $cursor);

        if ($fetched > 0) {
            $this->touchSyncState();
        }

        return $stats;
    }

    /**
     * @param  array<string, mixed>  $document
     */
    protected function importFormDocument(array $document, $webForm): void
    {
        $input = $this->formMapper->toSubmissionInput($document);
        $mapped = $this->submissionMapper->map($input, $webForm);

        $email = $mapped['person']['emails'][0]['value'] ?? null;

        if (! $email) {
            throw new \RuntimeException('Firestore form is missing an email address.');
        }

        $person = $this->personRepository->getModel()
            ->where('normalized_email', strtolower(trim($email)))
            ->first();

        $createLead = (bool) ($webForm->create_lead ?? true);

        if (! $createLead) {
            $person = $this->savePersonWithoutLead($mapped['person'], $person);
        } else {
            $person = $this->savePersonWithLead($mapped, $person);
        }

        $submittedAt = $mapped['submitted_at']
            ?? $this->documentTimestamp($document)
            ?? now();

        WebFormSubmission::query()->create([
            'web_form_id' => $webForm->id,
            'person_id' => $person->id,
            'payload' => array_merge($input, [
                'firestore_doc_id' => (string) ($document['id'] ?? ''),
            ]),
            'ip_address' => null,
            'user_agent' => 'firebase-sync',
            'spam_score' => 0,
            'status' => 'accepted',
            'created_at' => $submittedAt,
            'updated_at' => $submittedAt,
        ]);
    }

    /**
     * Persist person (+ org) only for retention / contact-only portal sync.
     *
     * @param  array<string, mixed>  $personData
     */
    protected function savePersonWithoutLead(array $personData, ?Person $person): Person
    {
        $personData = array_merge($personData, [
            'entity_type' => 'persons',
        ]);

        if ($person) {
            if (empty($person->lifecycle_stage)) {
                $personData['lifecycle_stage'] = LifecycleStage::Customer->value;
            } else {
                unset($personData['lifecycle_stage']);
            }

            return $this->personRepository->update($personData, $person->id);
        }

        if (empty($personData['lifecycle_stage']) || $personData['lifecycle_stage'] === LifecycleStage::Lead->value) {
            $personData['lifecycle_stage'] = LifecycleStage::Customer->value;
        }

        Event::dispatch('contacts.person.create.before');

        $person = $this->personRepository->create($personData);

        Event::dispatch('contacts.person.create.after', $person);

        return $person;
    }

    /**
     * Persist lead with nested person (existing portal sync path).
     *
     * @param  array{person: array<string, mixed>, lead: array<string, mixed>}  $mapped
     */
    protected function savePersonWithLead(array $mapped, ?Person $person): Person
    {
        $pipeline = $this->pipelineRepository->getDefaultPipeline();
        $stage = $pipeline->stages()->first();

        $data = array_merge($mapped['lead'], [
            'entity_type' => 'leads',
            'person' => $mapped['person'],
            'status' => 1,
            'lead_pipeline_id' => $pipeline->id,
            'lead_pipeline_stage_id' => $stage->id,
            'lead_value' => 0,
            'lead_type_id' => $this->typeRepository->first()?->id,
        ]);

        if (empty($data['lead_source_id'])) {
            $source = $this->sourceRepository->findOneByField('name', 'Web Form')
                ?: $this->sourceRepository->first();
            $data['lead_source_id'] = $source?->id;
        }

        if ($person) {
            $this->personRepository->update(array_merge($mapped['person'], [
                'entity_type' => 'persons',
            ]), $person->id);

            $data['person']['id'] = $person->id;
        }

        Event::dispatch('lead.create.before');

        $lead = $this->leadRepository->create($data);

        Event::dispatch('lead.create.after', $lead);

        return $lead->person ?? $this->personRepository->find($lead->person_id);
    }

    /**
     * @param  array<string, mixed>  $document
     */
    protected function documentTimestamp(array $document): ?Carbon
    {
        $field = (string) config('firebase.forms.date_field', 'submittedAt');

        if (empty($document[$field])) {
            return null;
        }

        try {
            return Carbon::parse($document[$field]);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function isAlreadySynced(string $docId): bool
    {
        if ($docId === '') {
            return false;
        }

        if (! DB::table('firebase_synced_documents')->where('firestore_doc_id', $docId)->exists()) {
            return false;
        }

        $hasSubmission = WebFormSubmission::query()
            ->where('user_agent', 'firebase-sync')
            ->where('payload->firestore_doc_id', $docId)
            ->exists();

        if (! $hasSubmission) {
            DB::table('firebase_synced_documents')->where('firestore_doc_id', $docId)->delete();

            return false;
        }

        return true;
    }

    /**
     * @return array{
     *     submissions: int,
     *     leads: int,
     *     persons: int,
     *     merge_pairs: int,
     *     organizations: int
     * }
     */
    public function resetSyncState(): array
    {
        return DB::transaction(function () {
            $stats = [
                'submissions' => 0,
                'leads' => 0,
                'persons' => 0,
                'merge_pairs' => 0,
                'organizations' => 0,
            ];

            $personIds = WebFormSubmission::query()
                ->where('user_agent', 'firebase-sync')
                ->pluck('person_id')
                ->unique()
                ->filter()
                ->values();

            $stats['submissions'] = WebFormSubmission::query()
                ->where('user_agent', 'firebase-sync')
                ->delete();

            if ($personIds->isNotEmpty()) {
                $stats['leads'] = Lead::query()
                    ->whereIn('person_id', $personIds)
                    ->delete();

                $importedOrgIds = Person::query()
                    ->whereIn('id', $personIds)
                    ->pluck('organization_id')
                    ->unique()
                    ->filter()
                    ->values();

                $stats['persons'] = Person::query()
                    ->whereIn('id', $personIds)
                    ->delete();
            } else {
                $importedOrgIds = collect();
            }

            $reviewPairs = OrganizationMergeReviewPair::query()
                ->get(['organization_a_id', 'organization_b_id']);

            $reviewOrgIds = $reviewPairs
                ->flatMap(fn (OrganizationMergeReviewPair $pair) => [
                    $pair->organization_a_id,
                    $pair->organization_b_id,
                ])
                ->unique()
                ->filter()
                ->values();

            $stats['merge_pairs'] = OrganizationMergeReviewPair::query()->delete();

            $candidateOrgIds = $importedOrgIds
                ->merge($reviewOrgIds)
                ->unique()
                ->values();

            if ($candidateOrgIds->isNotEmpty()) {
                $orphanOrgIds = Organization::query()
                    ->whereIn('id', $candidateOrgIds)
                    ->whereDoesntHave('persons')
                    ->pluck('id');

                if ($orphanOrgIds->isNotEmpty()) {
                    DB::table('omics_organization_aliases')
                        ->whereIn('organization_id', $orphanOrgIds)
                        ->delete();

                    $stats['organizations'] = Organization::query()
                        ->whereIn('id', $orphanOrgIds)
                        ->delete();
                }
            }

            DB::table('firebase_synced_documents')->delete();
            DB::table('firebase_sync_states')->where('collection', 'forms')->delete();

            return $stats;
        });
    }

    protected function markSynced(string $docId): void
    {
        $now = now();

        if ($this->isAlreadySynced($docId)) {
            DB::table('firebase_synced_documents')
                ->where('firestore_doc_id', $docId)
                ->update(['synced_at' => $now, 'updated_at' => $now]);

            return;
        }

        DB::table('firebase_synced_documents')->insert([
            'firestore_doc_id' => $docId,
            'synced_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    protected function lastSyncedAt(): ?Carbon
    {
        $value = DB::table('firebase_sync_states')
            ->where('collection', 'forms')
            ->value('last_synced_at');

        return $value ? Carbon::parse($value) : null;
    }

    protected function touchSyncState(): void
    {
        $now = now();

        if (DB::table('firebase_sync_states')->where('collection', 'forms')->exists()) {
            DB::table('firebase_sync_states')
                ->where('collection', 'forms')
                ->update(['last_synced_at' => $now, 'updated_at' => $now]);

            return;
        }

        DB::table('firebase_sync_states')->insert([
            'collection' => 'forms',
            'last_synced_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
