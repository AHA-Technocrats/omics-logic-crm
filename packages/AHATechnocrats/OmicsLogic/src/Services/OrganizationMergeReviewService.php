<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\OmicsLogic\Models\AuditLog;
use AHATechnocrats\OmicsLogic\Models\OrganizationMergeReviewPair;
use AHATechnocrats\User\Models\User;
use Illuminate\Support\Facades\DB;

class OrganizationMergeReviewService
{
    public function __construct(protected OrganizationNormalizer $normalizer) {}

    public function getQueueStats(): array
    {
        $weekAgo = now()->subDays(7);

        return [
            'pending' => OrganizationMergeReviewPair::query()->where('status', 'pending')->count(),
            'merged_7d' => AuditLog::query()
                ->where('action', 'merge_organizations')
                ->where('created_at', '>=', $weekAgo)
                ->count(),
            'kept_separate_7d' => OrganizationMergeReviewPair::query()
                ->where('status', 'separate')
                ->where('resolved_at', '>=', $weekAgo)
                ->count(),
            'avg_confidence' => (float) (OrganizationMergeReviewPair::query()
                ->where('status', 'pending')
                ->avg('confidence') ?? 0),
        ];
    }

    public function resolve(OrganizationMergeReviewPair $pair, string $action, int $userId): OrganizationMergeReviewPair
    {
        return match ($action) {
            'merge' => $this->merge($pair, $userId),
            'separate' => $this->resolveWithoutMerge($pair, 'separate', $userId),
            'dismiss' => $this->resolveWithoutMerge($pair, 'dismissed', $userId),
            default => throw new \InvalidArgumentException('Invalid merge review action.'),
        };
    }

    protected function merge(OrganizationMergeReviewPair $pair, int $userId): OrganizationMergeReviewPair
    {
        return DB::transaction(function () use ($pair, $userId) {
            $organizationA = Organization::query()->findOrFail($pair->organization_a_id);
            $organizationB = Organization::query()->findOrFail($pair->organization_b_id);

            [$survivor, $duplicate] = $this->pickSurvivor($organizationA, $organizationB);

            $duplicateBefore = $duplicate->only(['id', 'name', 'country_code']);

            $this->absorbOrganizationData($survivor, $duplicate);

            /**
             * Move every contact and alias to the survivor before removing the duplicate.
             */
            Person::query()
                ->where('organization_id', $duplicate->id)
                ->update([
                    'organization_id' => $survivor->id,
                    'country_code' => $survivor->country_code,
                ]);

            DB::table('omics_organization_aliases')
                ->where('organization_id', $duplicate->id)
                ->update(['organization_id' => $survivor->id]);

            $this->registerAlias($survivor, $duplicate->name);

            $survivor->contacts_count = Person::query()
                ->where('organization_id', $survivor->id)
                ->whereNull('merged_into_id')
                ->count();
            $survivor->save();

            $duplicate->delete();

            $pair->update([
                'status' => 'merged',
                'resolved_by' => $userId,
                'resolved_at' => now(),
            ]);

            AuditLogger::log(
                action: 'merge_organizations',
                entityType: 'organization',
                entityId: $survivor->id,
                before: ['duplicate' => $duplicateBefore, 'survivor_id' => $survivor->id],
                after: [
                    'pair_id' => $pair->id,
                    'duplicate_organization_id' => $duplicate->id,
                    'survivor_organization_id' => $survivor->id,
                    'survivor_name' => $survivor->name,
                ],
            );

            return $pair->fresh();
        });
    }

    protected function resolveWithoutMerge(OrganizationMergeReviewPair $pair, string $status, int $userId): OrganizationMergeReviewPair
    {
        $pair->update([
            'status' => $status,
            'resolved_by' => $userId,
            'resolved_at' => now(),
        ]);

        AuditLogger::log(
            action: $status === 'separate' ? 'keep_organizations_separate' : 'dismiss_organization_match',
            entityType: 'organization_merge_review_pair',
            entityId: $pair->id,
            after: [
                'pair_id' => $pair->id,
                'organization_a_id' => $pair->organization_a_id,
                'organization_b_id' => $pair->organization_b_id,
            ],
        );

        return $pair->fresh();
    }

    protected function pickSurvivor(Organization $organizationA, Organization $organizationB): array
    {
        $countA = (int) ($organizationA->contacts_count ?? 0);
        $countB = (int) ($organizationB->contacts_count ?? 0);

        if ($countA !== $countB) {
            return $countA >= $countB
                ? [$organizationA, $organizationB]
                : [$organizationB, $organizationA];
        }

        return $organizationA->id <= $organizationB->id
            ? [$organizationA, $organizationB]
            : [$organizationB, $organizationA];
    }

    protected function absorbOrganizationData(Organization $survivor, Organization $duplicate): void
    {
        foreach (['country_code', 'website', 'notes', 'account_owner_id', 'user_id', 'type'] as $field) {
            if (empty($survivor->{$field}) && ! empty($duplicate->{$field})) {
                $survivor->{$field} = $duplicate->{$field};
            }
        }

        $survivor->save();
    }

    protected function registerAlias(Organization $survivor, ?string $aliasName): void
    {
        if (! $aliasName || trim($aliasName) === '') {
            return;
        }

        $normalizedKey = $this->normalizer->normalize($aliasName);

        if ($normalizedKey === '' || $normalizedKey === $survivor->normalized_name) {
            return;
        }

        $exists = DB::table('omics_organization_aliases')
            ->where('organization_id', $survivor->id)
            ->where('alias_name', $aliasName)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('omics_organization_aliases')->insert([
            'organization_id' => $survivor->id,
            'alias_name' => $aliasName,
            'normalized_key' => $normalizedKey,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function organizationSnapshot(Organization $organization): array
    {
        return [
            'id' => $organization->id,
            'name' => $organization->name,
            'country' => $organization->country_code ?: '—',
            'type' => $organization->type ?: '—',
            'contacts' => Person::query()
                ->where('organization_id', $organization->id)
                ->whereNull('merged_into_id')
                ->count(),
            'owner' => $organization->account_owner_id
                ? (User::query()->find($organization->account_owner_id)?->name ?? '—')
                : '—',
            'added' => $organization->created_at?->format('M Y') ?? '—',
        ];
    }
}
