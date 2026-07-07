<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\Lead\Models\Source;
use AHATechnocrats\OmicsLogic\Models\AuditLog;
use AHATechnocrats\OmicsLogic\Models\MergeReviewPair;
use AHATechnocrats\Product\Models\Product;
use Illuminate\Support\Facades\DB;

class MergeReviewService
{
    public function getQueueStats(): array
    {
        $weekAgo = now()->subDays(7);

        return [
            'pending' => MergeReviewPair::query()->where('status', 'pending')->count(),
            'auto_merged_7d' => AuditLog::query()
                ->where('action', 'auto_merge_contacts')
                ->where('created_at', '>=', $weekAgo)
                ->count(),
            'kept_separate_7d' => MergeReviewPair::query()
                ->where('status', 'separate')
                ->where('resolved_at', '>=', $weekAgo)
                ->count(),
            'avg_confidence' => (float) (MergeReviewPair::query()
                ->where('status', 'pending')
                ->avg('confidence') ?? 0),
        ];
    }

    public function resolve(MergeReviewPair $pair, string $action, int $userId): MergeReviewPair
    {
        return match ($action) {
            'merge' => $this->merge($pair, $userId),
            'separate' => $this->resolveWithoutMerge($pair, 'separate', $userId),
            'dismiss' => $this->resolveWithoutMerge($pair, 'dismissed', $userId),
            default => throw new \InvalidArgumentException('Invalid merge review action.'),
        };
    }

    protected function merge(MergeReviewPair $pair, int $userId): MergeReviewPair
    {
        return DB::transaction(function () use ($pair, $userId) {
            $personA = Person::query()->findOrFail($pair->person_a_id);
            $personB = Person::query()->findOrFail($pair->person_b_id);

            [$survivor, $duplicate] = $this->pickSurvivor($personA, $personB);

            $duplicateBefore = $duplicate->only(['id', 'name', 'merged_into_id', 'emails']);

            $this->absorbPersonData($survivor, $duplicate);

            $duplicate->merged_into_id = $survivor->id;
            $duplicate->save();

            $pair->update([
                'status' => 'merged',
                'resolved_by' => $userId,
                'resolved_at' => now(),
            ]);

            AuditLogger::log(
                action: 'merge_contacts',
                entityType: 'merge_review_pair',
                entityId: $pair->id,
                before: ['duplicate' => $duplicateBefore, 'survivor_id' => $survivor->id],
                after: [
                    'pair_id' => $pair->id,
                    'duplicate_person_id' => $duplicate->id,
                    'survivor_person_id' => $survivor->id,
                    'survivor_name' => $survivor->name,
                ],
                reversible: true,
            );

            return $pair->fresh();
        });
    }

    protected function resolveWithoutMerge(MergeReviewPair $pair, string $status, int $userId): MergeReviewPair
    {
        $pair->update([
            'status' => $status,
            'resolved_by' => $userId,
            'resolved_at' => now(),
        ]);

        AuditLogger::log(
            action: $status === 'separate' ? 'keep_contacts_separate' : 'dismiss_merge_match',
            entityType: 'merge_review_pair',
            entityId: $pair->id,
            after: [
                'pair_id' => $pair->id,
                'person_a_id' => $pair->person_a_id,
                'person_b_id' => $pair->person_b_id,
            ],
        );

        return $pair->fresh();
    }

    protected function pickSurvivor(Person $personA, Person $personB): array
    {
        if (($personA->lead_score ?? 0) !== ($personB->lead_score ?? 0)) {
            return ($personA->lead_score ?? 0) >= ($personB->lead_score ?? 0)
                ? [$personA, $personB]
                : [$personB, $personA];
        }

        return $personA->id <= $personB->id ? [$personA, $personB] : [$personB, $personA];
    }

    protected function absorbPersonData(Person $survivor, Person $duplicate): void
    {
        $survivorEmails = collect($survivor->emails ?? []);
        $duplicateEmails = collect($duplicate->emails ?? []);
        $survivor->emails = $survivorEmails
            ->merge($duplicateEmails)
            ->unique('value')
            ->values()
            ->all();

        $survivorPhones = collect($survivor->contact_numbers ?? []);
        $duplicatePhones = collect($duplicate->contact_numbers ?? []);
        $survivor->contact_numbers = $survivorPhones
            ->merge($duplicatePhones)
            ->unique('value')
            ->values()
            ->all();

        $survivor->engagement_lessons = max(
            (int) ($survivor->engagement_lessons ?? 0),
            (int) ($duplicate->engagement_lessons ?? 0),
        );

        $survivor->lead_score = max(
            (int) ($survivor->lead_score ?? 0),
            (int) ($duplicate->lead_score ?? 0),
        );

        foreach (['education_level', 'primary_product_id', 'primary_source_id', 'organization_id', 'job_title'] as $field) {
            if (empty($survivor->{$field}) && ! empty($duplicate->{$field})) {
                $survivor->{$field} = $duplicate->{$field};
            }
        }

        if ($survivor->organization_id) {
            $survivor->country_code = $survivor->organization?->country_code;
        }

        $survivor->last_activity_at = collect([
            $survivor->last_activity_at,
            $duplicate->last_activity_at,
        ])->filter()->max();

        $survivor->save();
    }

    public function personSnapshot(Person $person): array
    {
        $email = collect($person->emails ?? [])->pluck('value')->first();
        $phone = collect($person->contact_numbers ?? [])->pluck('value')->first();

        return [
            'id' => $person->id,
            'name' => $person->name,
            'email' => $email,
            'phone' => $phone ?: '—',
            'organization' => $person->organization?->name ?? '—',
            'program' => $person->primary_product_id
                ? Product::query()->find($person->primary_product_id)?->name
                : '—',
            'source' => $person->primary_source_id
                ? Source::query()->find($person->primary_source_id)?->name
                : '—',
            'added' => $person->created_at?->format('M Y') ?? '—',
        ];
    }
}
