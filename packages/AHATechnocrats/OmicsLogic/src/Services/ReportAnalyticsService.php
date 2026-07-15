<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\Lead\Models\Source;
use AHATechnocrats\Product\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportAnalyticsService
{
    public function buildReport(array $filters): array
    {
        $query = $this->baseQuery($filters);

        $total = (clone $query)->count();
        $engaged = (clone $query)->where('engagement_lessons', '>', 0)->count();
        $customers = (clone $query)->whereHas('leads.stage', function ($q) {
            $q->where('code', 'won');
        })->count();

        $monthsInRange = $this->monthsInRange($filters);
        $monthFrom = min($filters['month_from'], $filters['month_to']);
        $monthTo = max($filters['month_from'], $filters['month_to']);

        return [
            'summary' => [
                'leads_in_range' => $total,
                'engaged' => $engaged,
                'customers' => $customers,
                'engaged_rate' => $total > 0 ? round(($engaged / $total) * 100, 1) : 0,
                'conversion_rate' => $total > 0 ? round(($customers / $total) * 100, 1) : 0,
                'avg_per_month' => round($total / max(1, $monthsInRange)),
                'months_in_range' => $monthsInRange,
                'range_label' => sprintf(
                    '%s–%s %d',
                    date('M', mktime(0, 0, 0, $monthFrom, 1)),
                    date('M', mktime(0, 0, 0, $monthTo, 1)),
                    $filters['year']
                ),
            ],
            'by_month' => $this->groupByMonth($query, $filters),
            'by_organization' => $this->groupByOrganization($query),
            'by_education' => $this->groupByField($query, 'education_level'),
            'by_source' => $this->groupBySource($query),
            'by_program' => $this->groupByProgram($query),
            'by_country' => $this->groupByField($query, 'country_code'),
            'funnel' => $this->lifecycleFunnel($query, $total, $engaged, $customers),
            'lessons' => $this->lessonsBreakdown($query),
            'engaged_by_program' => $this->engagedByProgram($query),
            'program_completion' => $this->programCompletionRates($query),
            'yoy' => $this->yearOverYear($filters),
            'filters' => $filters,
        ];
    }

    public function defaultFilters(): array
    {
        return [
            'year' => (int) now()->year,
            'month_from' => 1,
            'month_to' => (int) now()->month,
            'organization' => '',
            'education' => '',
            'source' => '',
            'program' => '',
        ];
    }

    public function normalizeFilters(array $input): array
    {
        $defaults = $this->defaultFilters();

        return [
            'year' => (int) ($input['year'] ?? $defaults['year']),
            'month_from' => max(1, min(12, (int) ($input['month_from'] ?? $defaults['month_from']))),
            'month_to' => max(1, min(12, (int) ($input['month_to'] ?? $defaults['month_to']))),
            'organization' => trim((string) ($input['organization'] ?? '')),
            'education' => trim((string) ($input['education'] ?? '')),
            'source' => trim((string) ($input['source'] ?? '')),
            'program' => trim((string) ($input['program'] ?? '')),
        ];
    }

    protected function baseQuery(array $filters): Builder
    {
        $from = sprintf('%04d-%02d-01', $filters['year'], min($filters['month_from'], $filters['month_to']));
        $toMonth = max($filters['month_from'], $filters['month_to']);
        $to = sprintf('%04d-%02d-%02d', $filters['year'], $toMonth, (int) now()->setDate($filters['year'], $toMonth, 1)->endOfMonth()->format('d'));

        $query = Person::query()
            ->whereNull('merged_into_id')
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']);

        if ($filters['organization'] !== '') {
            $orgIds = Organization::query()
                ->where('name', $filters['organization'])
                ->pluck('id');

            $query->whereIn('organization_id', $orgIds);
        }

        if ($filters['education'] !== '') {
            $query->where('education_level', $filters['education']);
        }

        if ($filters['source'] !== '') {
            $sourceIds = Source::query()
                ->where('name', $filters['source'])
                ->pluck('id');

            $query->whereIn('primary_source_id', $sourceIds);
        }

        if ($filters['program'] !== '') {
            $productIds = Product::query()
                ->where('name', $filters['program'])
                ->pluck('id');

            $query->whereIn('primary_product_id', $productIds);
        }

        return $query;
    }

    protected function monthsInRange(array $filters): int
    {
        return max(1, abs($filters['month_to'] - $filters['month_from']) + 1);
    }

    protected function groupByMonth(Builder $query, array $filters): Collection
    {
        $rows = (clone $query)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $start = min($filters['month_from'], $filters['month_to']);
        $end = max($filters['month_from'], $filters['month_to']);

        return collect(range($start, $end))->mapWithKeys(function (int $month) use ($rows) {
            return [date('M', mktime(0, 0, 0, $month, 1)) => (int) ($rows[$month] ?? 0)];
        });
    }

    protected function groupByOrganization(Builder $query): Collection
    {
        $results = (clone $query)
            ->selectRaw('organization_id, COUNT(*) as total')
            ->whereNotNull('organization_id')
            ->groupBy('organization_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->pluck('total', 'organization_id');

        $orgs = Organization::query()->orderBy('name')->limit(8)->get();

        if ($orgs->isEmpty() && $results->isEmpty()) {
            return collect();
        }

        return $orgs->map(function ($org) use ($results) {
            return [
                'label' => $org->name,
                'total' => (int) ($results[$org->id] ?? 0),
            ];
        })->sortByDesc('total')->values();
    }

    protected function groupByField(Builder $query, string $field): Collection
    {
        $results = (clone $query)
            ->selectRaw($field.' as label, COUNT(*) as total')
            ->whereNotNull($field)
            ->where($field, '!=', '')
            ->groupBy($field)
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->pluck('total', 'label');

        if ($field === 'education_level') {
            $defaultLabels = ['Undergraduate', 'Masters', 'PhD', 'Faculty', 'Industry'];
        } else {
            $defaultLabels = Person::query()
                ->whereNotNull('country_code')
                ->where('country_code', '!=', '')
                ->distinct()
                ->limit(8)
                ->pluck('country_code')
                ->toArray();

            if (empty($defaultLabels)) {
                $defaultLabels = ['US', 'IN', 'GB', 'CA', 'AU'];
            }
        }

        return collect($defaultLabels)->map(function ($label) use ($results) {
            return [
                'label' => $label,
                'total' => (int) ($results[$label] ?? 0),
            ];
        })->sortByDesc('total')->values();
    }

    protected function groupBySource(Builder $query): Collection
    {
        $results = (clone $query)
            ->selectRaw('primary_source_id, COUNT(*) as total')
            ->whereNotNull('primary_source_id')
            ->groupBy('primary_source_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->pluck('total', 'primary_source_id');

        $sources = Source::query()->orderBy('name')->limit(8)->get();

        if ($sources->isEmpty() && $results->isEmpty()) {
            return collect();
        }

        return $sources->map(function ($source) use ($results) {
            return [
                'label' => $source->name,
                'total' => (int) ($results[$source->id] ?? 0),
            ];
        })->sortByDesc('total')->values();
    }

    protected function groupByProgram(Builder $query): Collection
    {
        $results = (clone $query)
            ->selectRaw('primary_product_id, COUNT(*) as total')
            ->whereNotNull('primary_product_id')
            ->groupBy('primary_product_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->pluck('total', 'primary_product_id');

        $products = Product::query()->orderBy('name')->limit(8)->get();

        if ($products->isEmpty() && $results->isEmpty()) {
            return collect();
        }

        return $products->map(function ($product) use ($results) {
            return [
                'label' => $product->name,
                'total' => (int) ($results[$product->id] ?? 0),
            ];
        })->sortByDesc('total')->values();
    }

    protected function lifecycleFunnel(Builder $query, int $total, int $engaged, int $customers): Collection
    {
        return collect([
            ['label' => 'All contacts', 'total' => $total],
            ['label' => 'Engaged', 'total' => $engaged],
            ['label' => 'Customers', 'total' => $customers],
        ]);
    }

    protected function engagedByProgram(Builder $query): Collection
    {
        $results = (clone $query)
            ->where('engagement_lessons', '>', 0)
            ->selectRaw('primary_product_id, COUNT(*) as total')
            ->whereNotNull('primary_product_id')
            ->groupBy('primary_product_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->pluck('total', 'primary_product_id');

        $products = Product::query()->orderBy('name')->limit(8)->get();

        if ($products->isEmpty() && $results->isEmpty()) {
            return collect();
        }

        return $products->map(function ($product) use ($results) {
            return [
                'label' => $product->name,
                'total' => (int) ($results[$product->id] ?? 0),
            ];
        })->sortByDesc('total')->values();
    }

    protected function programCompletionRates(Builder $query): Collection
    {
        $results = (clone $query)
            ->selectRaw('primary_product_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN engagement_lessons > 0 THEN 1 ELSE 0 END) as engaged')
            ->whereNotNull('primary_product_id')
            ->groupBy('primary_product_id')
            ->get()
            ->keyBy('primary_product_id');

        $products = Product::query()->orderBy('name')->limit(8)->get();

        if ($products->isEmpty() && $results->isEmpty()) {
            return collect();
        }

        return $products->map(function ($product) use ($results) {
            $row = $results[$product->id] ?? null;
            $rate = ($row && $row->total > 0) ? (int) round(($row->engaged / $row->total) * 100) : 0;

            return [
                'label' => $product->name,
                'total' => $rate,
            ];
        })->sortByDesc('total')->values();
    }

    protected function lessonsBreakdown(Builder $query): array
    {
        $withLessons = (clone $query)->where('engagement_lessons', '>', 0)->count();
        $total = (clone $query)->count();

        return [
            'with_lessons' => $withLessons,
            'without_lessons' => max(0, $total - $withLessons),
            'completion_rate' => $total > 0 ? round(($withLessons / $total) * 100, 1) : 0,
        ];
    }

    protected function yearOverYear(array $filters): Collection
    {
        $currentYear = $filters['year'];
        $years = range($currentYear - 3, $currentYear);

        return collect($years)->map(function (int $year) use ($filters) {
            $yearFilters = array_merge($filters, ['year' => $year]);

            return [
                'label' => (string) $year,
                'total' => $this->baseQuery($yearFilters)->count(),
                'selected' => $year === $filters['year'],
            ];
        });
    }

    public function exportRows(array $filters): Collection
    {
        return $this->baseQuery($filters)
            ->with(['organization'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Person $person) {
                $email = collect($person->emails ?? [])->pluck('value')->first();

                return [
                    'name' => $person->name,
                    'email' => $email,
                    'organization' => $person->organization?->name,
                    'country' => $person->country_code,
                    'education' => $person->education_level,
                    'lessons' => $person->engagement_lessons,
                    'score' => $person->lead_score,
                    'created_at' => $person->created_at?->toDateTimeString(),
                ];
            });
    }
}
