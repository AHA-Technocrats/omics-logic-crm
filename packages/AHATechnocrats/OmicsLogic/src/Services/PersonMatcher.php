<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\OmicsLogic\Models\MergeReviewPair;

class PersonMatcher
{
    public const ACTION_LINK = 'link';

    public const ACTION_REVIEW = 'review';

    public const ACTION_NEW = 'new';

    public function __construct(protected OrganizationNormalizer $normalizer) {}

    /**
     * Decide how an incoming contact relates to existing records.
     *
     * @return array{action: string, person_id: ?int, confidence: float, signals: array<int, string>}
     */
    public function match(?string $name, ?string $email, ?string $phone, ?int $organizationId = null): array
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $normalizedPhone = $this->normalizePhone($phone);

        /**
         * Exact identifier match — the strongest signal, treated as the same person.
         */
        if ($normalizedEmail) {
            $person = Person::query()
                ->whereNull('merged_into_id')
                ->where('normalized_email', $normalizedEmail)
                ->first();

            if ($person) {
                return $this->result(self::ACTION_LINK, $person->id, 1.0, ['Same email']);
            }
        }

        if ($normalizedPhone) {
            $person = Person::query()
                ->whereNull('merged_into_id')
                ->where('normalized_phone', $normalizedPhone)
                ->first();

            if ($person) {
                return $this->result(self::ACTION_LINK, $person->id, 1.0, ['Same phone']);
            }
        }

        /**
         * No identifier match — fall back to fuzzy name matching.
         */
        if (! $name || trim($name) === '') {
            return $this->result(self::ACTION_NEW, null, 0.0, []);
        }

        [$bestMatch, $bestScore] = $this->bestNameMatch($name, $organizationId);

        if (! $bestMatch) {
            return $this->result(self::ACTION_NEW, null, 0.0, []);
        }

        $autoThreshold = (float) config('omicslogic.dedup.auto_merge_threshold', 0.95);
        $reviewThreshold = (float) config('omicslogic.dedup.review_threshold', 0.70);

        $signals = ["“{$name}” ≈ “{$bestMatch->name}”"];

        if ($organizationId && (int) $bestMatch->organization_id === (int) $organizationId) {
            $signals[] = 'Same organization';
        }

        if ($bestScore >= $autoThreshold) {
            return $this->result(self::ACTION_LINK, $bestMatch->id, $bestScore, $signals);
        }

        if ($bestScore >= $reviewThreshold) {
            return $this->result(self::ACTION_REVIEW, $bestMatch->id, $bestScore, $signals);
        }

        return $this->result(self::ACTION_NEW, null, $bestScore, []);
    }

    /**
     * Queue a possible-duplicate pair for human review.
     */
    public function queuePair(int $existingId, int $newId, float $confidence, array $signals = []): void
    {
        if ($existingId === $newId) {
            return;
        }

        $personA = min($existingId, $newId);
        $personB = max($existingId, $newId);

        MergeReviewPair::query()->firstOrCreate(
            [
                'person_a_id' => $personA,
                'person_b_id' => $personB,
            ],
            [
                'confidence' => round($confidence, 2),
                'match_signals' => array_values(array_filter($signals)),
                'status' => 'pending',
            ],
        );
    }

    /**
     * Best fuzzy name match, scoped to the organization when known.
     *
     * @return array{0: ?Person, 1: float}
     */
    protected function bestNameMatch(string $name, ?int $organizationId): array
    {
        $bestMatch = null;
        $bestScore = 0.0;

        $firstWord = strtok(trim($name), ' ') ?: trim($name);

        Person::query()
            ->select(['id', 'name', 'organization_id'])
            ->whereNull('merged_into_id')
            ->whereNotNull('name')
            ->when(
                $organizationId,
                fn ($query) => $query->where('organization_id', $organizationId),
                fn ($query) => $query->where('name', 'like', '%'.$firstWord.'%'),
            )
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

    protected function normalizeEmail(?string $email): ?string
    {
        if (! $email || trim($email) === '') {
            return null;
        }

        return strtolower(trim($email));
    }

    protected function normalizePhone(?string $phone): ?string
    {
        if (! $phone || trim($phone) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        return $digits === '' ? null : $digits;
    }

    protected function result(string $action, ?int $personId, float $confidence, array $signals): array
    {
        return [
            'action' => $action,
            'person_id' => $personId,
            'confidence' => round($confidence, 2),
            'signals' => array_values(array_filter($signals)),
        ];
    }
}
