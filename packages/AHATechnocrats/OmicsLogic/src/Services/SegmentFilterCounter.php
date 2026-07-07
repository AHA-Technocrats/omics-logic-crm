<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\OmicsLogic\Models\Segment;

class SegmentFilterCounter
{
    public function countForFilters(array $filters): int
    {
        $query = Person::query();

        if (! empty($filters['country_code'])) {
            $query->where('country_code', $filters['country_code']);
        }

        if (! empty($filters['lifecycle_stage'])) {
            $query->where('lifecycle_stage', $filters['lifecycle_stage']);
        }

        if (! empty($filters['education_level'])) {
            $query->where('education_level', $filters['education_level']);
        }

        if (! empty($filters['primary_product_id'])) {
            $query->where('primary_product_id', $filters['primary_product_id']);
        }

        if (! empty($filters['primary_source_id'])) {
            $query->where('primary_source_id', $filters['primary_source_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (($filters['engagement'] ?? '') === 'yes') {
            $query->where('engagement_lessons', '>', 0);
        } elseif (($filters['engagement'] ?? '') === 'no') {
            $query->where(function ($q) {
                $q->whereNull('engagement_lessons')->orWhere('engagement_lessons', 0);
            });
        }

        return $query->count();
    }

    public function refreshSegment(Segment $segment): Segment
    {
        $segment->contact_count_cached = $this->countForFilters($segment->filter_query ?? []);
        $segment->last_refreshed_at = now();
        $segment->save();

        return $segment;
    }

    public function buildFilterQueryFromRequest(array $data): array
    {
        return array_filter([
            'country_code' => $data['filter_country_code'] ?? null,
            'lifecycle_stage' => $data['filter_lifecycle_stage'] ?? null,
            'education_level' => $data['filter_education_level'] ?? null,
            'primary_product_id' => $data['filter_primary_product_id'] ?? null,
            'primary_source_id' => $data['filter_primary_source_id'] ?? null,
            'user_id' => $data['filter_user_id'] ?? null,
            'engagement' => $data['filter_engagement'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    }
}
