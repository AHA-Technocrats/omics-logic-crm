<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\Contact\Repositories\OrganizationRepository;
use AHATechnocrats\OmicsLogic\Enums\OrganizationType;
use AHATechnocrats\OmicsLogic\Models\OrganizationMergeReviewPair;

class OrganizationResolver
{
    /**
     * Fuzzy similarity at or above which two organizations are treated as the
     * same record without review (legacy default when review queuing is off).
     */
    protected const AUTO_LINK_SIMILARITY = 0.92;

    public function __construct(
        protected OrganizationRepository $organizationRepository,
        protected OrganizationNormalizer $normalizer,
    ) {}

    /**
     * Resolve an organization by name.
     *
     * When $queueReview is true (imports), a medium-confidence fuzzy match
     * creates a NEW organization and queues an org merge-review pair so a human
     * can confirm the merge later. Exact/alias/high matches always link.
     */
    public function resolve(
        ?string $name,
        bool $allowCreate = true,
        ?string $countryCode = null,
        bool $queueReview = false,
    ): ?Organization {
        if (! $name || trim($name) === '') {
            return null;
        }

        $normalizedKey = $this->normalizer->normalize($name);

        $existing = Organization::query()
            ->where('normalized_name', $normalizedKey)
            ->first();

        if ($existing) {
            return $this->applyCountry($existing, $countryCode);
        }

        $aliasMatch = \DB::table('omics_organization_aliases')
            ->where('normalized_key', $normalizedKey)
            ->first();

        if ($aliasMatch) {
            $matchedOrg = Organization::find($aliasMatch->organization_id);
            if ($matchedOrg) {
                return $this->applyCountry($matchedOrg, $countryCode);
            }
        }

        [$bestMatch, $bestScore] = $this->bestFuzzyMatch($name);

        $autoThreshold = $queueReview
            ? (float) config('omicslogic.dedup.auto_merge_threshold', 0.95)
            : self::AUTO_LINK_SIMILARITY;

        if ($bestMatch && $bestScore >= $autoThreshold) {
            return $this->applyCountry($bestMatch, $countryCode);
        }

        if (! $allowCreate) {
            return null;
        }

        $organization = $this->organizationRepository->create([
            'name' => trim($name),
            'normalized_name' => $normalizedKey,
            'type' => OrganizationType::Other->value,
            'country_code' => $countryCode,
            'entity_type' => 'organizations',
        ]);

        if (
            $queueReview
            && $bestMatch
            && $bestScore >= (float) config('omicslogic.dedup.review_threshold', 0.70)
        ) {
            $this->queueReviewPair($bestMatch, $organization, $bestScore);
        }

        return $organization;
    }

    /**
     * Find the most similar existing organization to a name.
     *
     * @return array{0: ?Organization, 1: float}
     */
    protected function bestFuzzyMatch(string $name): array
    {
        $bestMatch = null;
        $bestScore = 0.0;

        Organization::query()->select(['id', 'name', 'country_code', 'normalized_name', 'contacts_count'])
            ->chunk(500, function ($candidates) use ($name, &$bestMatch, &$bestScore) {
                foreach ($candidates as $candidate) {
                    $score = $this->normalizer->similarity($candidate->name, $name);

                    if ($score > $bestScore) {
                        $bestScore = $score;
                        $bestMatch = $candidate;
                    }
                }
            });

        return [$bestMatch, $bestScore];
    }

    protected function queueReviewPair(Organization $existing, Organization $created, float $score): void
    {
        OrganizationMergeReviewPair::query()->firstOrCreate(
            [
                'organization_a_id' => $existing->id,
                'organization_b_id' => $created->id,
            ],
            [
                'confidence' => round($score, 2),
                'match_signals' => [
                    'Similar name',
                    "“{$created->name}” ≈ “{$existing->name}”",
                ],
                'status' => 'pending',
            ],
        );
    }

    protected function applyCountry(Organization $organization, ?string $countryCode): Organization
    {
        if ($countryCode && $organization->country_code !== $countryCode) {
            $organization->country_code = $countryCode;
            $organization->save();

            \DB::table('persons')
                ->where('organization_id', $organization->id)
                ->whereNull('merged_into_id')
                ->update(['country_code' => $countryCode]);
        }

        return $organization;
    }
}
