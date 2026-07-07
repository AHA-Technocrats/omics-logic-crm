<?php

namespace AHATechnocrats\Admin\Http\Controllers\OmicsLogic;

use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\Lead\Models\Source;
use AHATechnocrats\OmicsLogic\Services\ReportAnalyticsService;
use AHATechnocrats\Product\Models\Product;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(ReportAnalyticsService $analytics): View
    {
        $filters = $analytics->normalizeFilters(request()->all());
        $report = $analytics->buildReport($filters);

        $filterOptions = [
            'education_levels' => ['Undergraduate', 'Masters', 'PhD', 'Faculty', 'Industry'],
            'years' => range((int) now()->year, (int) now()->year - 5),
            'organizations' => Organization::query()->orderBy('name')->pluck('name'),
            'sources' => Source::query()->orderBy('name')->pluck('name'),
            'programs' => Product::query()->orderBy('name')->pluck('name'),
        ];

        return view('admin::omics.reports.index', compact('report', 'filters', 'filterOptions'));
    }

    public function export(ReportAnalyticsService $analytics): StreamedResponse
    {
        $filters = $analytics->normalizeFilters(request()->all());
        $rows = $analytics->exportRows($filters);

        $filename = sprintf('omics-report-%d-%02d-%02d.csv', $filters['year'], $filters['month_from'], $filters['month_to']);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Name', 'Email', 'Organization', 'Country', 'Stage', 'Education', 'Lessons', 'Score', 'Created At']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['name'],
                    $row['email'],
                    $row['organization'],
                    $row['country'],
                    $row['stage'],
                    $row['education'],
                    $row['lessons'],
                    $row['score'],
                    $row['created_at'],
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
