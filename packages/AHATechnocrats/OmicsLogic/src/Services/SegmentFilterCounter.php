<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Lead\Models\Source;
use AHATechnocrats\OmicsLogic\Models\Segment;
use AHATechnocrats\Product\Models\Product;
use AHATechnocrats\User\Models\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class SegmentFilterCounter
{
    public function countForFilters(array $filters): int
    {
        $query = DB::table('persons')->whereNull('persons.merged_into_id');

        $this->applyFiltersToQuery($query, $filters, 'persons');

        return $query->count();
    }

    public function countLeadsForFilters(array $filters): int
    {
        $tablePrefix = DB::getTablePrefix();

        $query = DB::table('leads')
            ->join('persons', 'leads.person_id', '=', 'persons.id')
            ->whereNull('persons.merged_into_id');

        $this->applyFiltersToQuery($query, $filters, 'persons');

        return $query->count(DB::raw('DISTINCT '.$tablePrefix.'leads.id'));
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public function describeFilters(array $filters): array
    {
        $labels = [];

        if (! empty($filters['country_code'])) {
            $labels[] = [
                'label' => trans('omicslogic::app.fields.country'),
                'value' => (string) $filters['country_code'],
            ];
        }

        if (! empty($filters['education_level'])) {
            $labels[] = [
                'label' => trans('omicslogic::app.fields.education'),
                'value' => (string) $filters['education_level'],
            ];
        }

        if (! empty($filters['primary_product_id'])) {
            $product = Product::query()->find($filters['primary_product_id']);

            $labels[] = [
                'label' => trans('omicslogic::app.fields.campaign'),
                'value' => $product?->name ?? (string) $filters['primary_product_id'],
            ];
        }

        if (! empty($filters['primary_source_id'])) {
            $source = Source::query()->find($filters['primary_source_id']);

            $labels[] = [
                'label' => trans('omicslogic::app.fields.source'),
                'value' => $source?->name ?? (string) $filters['primary_source_id'],
            ];
        }

        if (! empty($filters['user_id'])) {
            $user = User::query()->find($filters['user_id']);

            $labels[] = [
                'label' => trans('omicslogic::app.fields.owner'),
                'value' => $user?->name ?? (string) $filters['user_id'],
            ];
        }

        if (($filters['engagement'] ?? '') === 'yes') {
            $labels[] = [
                'label' => trans('omicslogic::app.fields.engagement'),
                'value' => trans('omicslogic::app.fields.engagement-yes'),
            ];
        } elseif (($filters['engagement'] ?? '') === 'no') {
            $labels[] = [
                'label' => trans('omicslogic::app.fields.engagement'),
                'value' => trans('omicslogic::app.fields.engagement-no'),
            ];
        }

        return $labels;
    }

    public function applyFiltersToQuery(Builder|EloquentBuilder $query, array $filters, string $table = 'persons'): Builder|EloquentBuilder
    {
        if (! empty($filters['country_code'])) {
            $query->where("{$table}.country_code", $filters['country_code']);
        }

        if (! empty($filters['education_level'])) {
            $query->where("{$table}.education_level", $filters['education_level']);
        }

        if (! empty($filters['primary_product_id'])) {
            $query->where("{$table}.primary_product_id", $filters['primary_product_id']);
        }

        if (! empty($filters['primary_source_id'])) {
            $query->where("{$table}.primary_source_id", $filters['primary_source_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where("{$table}.user_id", $filters['user_id']);
        }

        if (($filters['engagement'] ?? '') === 'yes') {
            $query->where("{$table}.engagement_lessons", '>', 0);
        } elseif (($filters['engagement'] ?? '') === 'no') {
            $query->where(function ($q) use ($table) {
                $q->whereNull("{$table}.engagement_lessons")
                    ->orWhere("{$table}.engagement_lessons", 0);
            });
        }

        return $query;
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
            'education_level' => $data['filter_education_level'] ?? null,
            'primary_product_id' => $data['filter_primary_product_id'] ?? null,
            'primary_source_id' => $data['filter_primary_source_id'] ?? null,
            'user_id' => $data['filter_user_id'] ?? null,
            'engagement' => $data['filter_engagement'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    }
}
