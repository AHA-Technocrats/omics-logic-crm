<x-admin::layouts>
    <x-slot:title>
        @lang('omicslogic::app.reports.title')
    </x-slot>

    @include('admin::omics.partials.report-charts-styles')

    @php
        $summary = $report['summary'];
        $byMonthItems = $report['by_month']->map(fn ($total, $label) => ['label' => $label, 'total' => $total])->values();
        $countryItems = $report['by_country']->map(function ($item) {
            $label = $item['label'];
            if ($label && $label !== '—') {
                $label = app(\AHATechnocrats\OmicsLogic\Services\CountryLabelResolver::class)->resolve($label) ?? $label;
            }

            return ['label' => $label, 'total' => $item['total']];
        });
        $hasActiveFilters = $filters['organization'] || $filters['education'] || $filters['source'] || $filters['program'];

        $byProgramItems = $report['by_program']->isNotEmpty()
            ? $report['by_program']
            : collect([['label' => __('omicslogic::app.reports.unassigned-program'), 'total' => $summary['leads_in_range']]]);

        $engagedByProgramItems = $report['engaged_by_program']->isNotEmpty()
            ? $report['engaged_by_program']
            : collect([['label' => __('omicslogic::app.reports.overall-engagement'), 'total' => $summary['engaged']]]);

        $programCompletionItems = $report['program_completion']->isNotEmpty()
            ? $report['program_completion']
            : collect([['label' => __('omicslogic::app.reports.overall-engagement'), 'total' => (int) $report['lessons']['completion_rate']]]);
    @endphp

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <div class="grid gap-1">
                <x-admin::breadcrumbs name="omics.reports" />

                <h1 class="text-2xl font-bold dark:text-white">
                    @lang('omicslogic::app.reports.title')
                </h1>

                <p class="text-sm text-gray-600 dark:text-gray-300">
                    @lang('omicslogic::app.reports.subtitle')
                    @if ($hasActiveFilters)
                        <span class="font-semibold text-brandColor">@lang('omicslogic::app.reports.filtered-active')</span>
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <!-- Export CSV Button matching system styling -->
                <a
                    href="{{ route('admin.omics.reports.export', request()->query()) }}"
                    class="secondary-button py-[9px]"
                >
                    <span class="icon-download text-lg"></span>
                    @lang('omicslogic::app.reports.export-csv')
                </a>

                <!-- Filter Drawer matching system datagrid filters styling -->
                <x-admin::drawer
                    width="350px"
                    ref="reportFilterDrawer"
                >
                    <x-slot:toggle>
                        <button class="relative flex cursor-pointer items-center rounded-md bg-sky-100 px-4 py-[9px] font-semibold text-sky-600 dark:bg-brandColor dark:text-white">
                            <span class="icon-filter text-xl ltr:mr-1.5 rtl:ml-1.5"></span>
                            @lang('admin::app.components.datagrid.toolbar.filter.title')

                            @if ($hasActiveFilters)
                                <span class="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-sky-600 dark:bg-white"></span>
                            @endif
                        </button>
                    </x-slot>

                    <x-slot:header class="p-3.5 border-b dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <p class="text-xl font-semibold dark:text-white">
                                @lang('admin::app.components.datagrid.filters.title')
                            </p>
                            @if ($hasActiveFilters)
                                <a href="{{ route('admin.omics.reports.index') }}" class="cursor-pointer text-xs font-medium text-brandColor hover:underline">
                                    @lang('admin::app.components.datagrid.filters.custom-filters.clear-all')
                                </a>
                            @endif
                        </div>
                    </x-slot>

                    <x-slot:content class="p-4">
                        <form
                            method="GET"
                            action="{{ route('admin.omics.reports.index') }}"
                            id="report-filters-form"
                            class="flex flex-col gap-4"
                        >
                            <div class="grid gap-4">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">@lang('omicslogic::app.reports.year')</label>
                                    <select name="year" class="custom-select w-full rounded border border-gray-300 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400">
                                        @foreach ($filterOptions['years'] as $year)
                                            <option value="{{ $year }}" @selected($filters['year'] == $year)>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">@lang('omicslogic::app.reports.month-from')</label>
                                    <select name="month_from" class="custom-select w-full rounded border border-gray-300 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400">
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" @selected($filters['month_from'] == $m)>{{ date('M', mktime(0, 0, 0, $m, 1)) }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">@lang('omicslogic::app.reports.month-to')</label>
                                    <select name="month_to" class="custom-select w-full rounded border border-gray-300 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400">
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" @selected($filters['month_to'] == $m)>{{ date('M', mktime(0, 0, 0, $m, 1)) }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">@lang('omicslogic::app.reports.organization')</label>
                                    <select name="organization" class="custom-select w-full rounded border border-gray-300 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400">
                                        <option value="">@lang('omicslogic::app.reports.all-organizations')</option>
                                        @foreach ($filterOptions['organizations'] as $organization)
                                            <option value="{{ $organization }}" @selected($filters['organization'] === $organization)>{{ $organization }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">@lang('omicslogic::app.reports.education')</label>
                                    <select name="education" class="custom-select w-full rounded border border-gray-300 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400">
                                        <option value="">@lang('omicslogic::app.reports.all-education')</option>
                                        @foreach ($filterOptions['education_levels'] as $level)
                                            <option value="{{ $level }}" @selected($filters['education'] === $level)>{{ $level }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">@lang('omicslogic::app.reports.source')</label>
                                    <select name="source" class="custom-select w-full rounded border border-gray-300 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400">
                                        <option value="">@lang('omicslogic::app.reports.all-sources')</option>
                                        @foreach ($filterOptions['sources'] as $source)
                                            <option value="{{ $source }}" @selected($filters['source'] === $source)>{{ $source }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">@lang('omicslogic::app.reports.program')</label>
                                    <select name="program" class="custom-select w-full rounded border border-gray-300 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400">
                                        <option value="">@lang('omicslogic::app.reports.all-programs')</option>
                                        @foreach ($filterOptions['programs'] as $program)
                                            <option value="{{ $program }}" @selected($filters['program'] === $program)>{{ $program }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </form>
                    </x-slot>

                    <x-slot:footer class="p-4 flex gap-2.5">
                        <button
                            type="button"
                            onclick="document.getElementById('report-filters-form').submit()"
                            class="primary-button flex-1 justify-center"
                        >
                            @lang('admin::app.components.datagrid.filters.custom-filters.apply')
                        </button>
                        <a
                            href="{{ route('admin.omics.reports.index') }}"
                            class="secondary-button flex-1 justify-center py-[9px]"
                        >
                            @lang('omicslogic::app.reports.reset-filters')
                        </a>
                    </x-slot:footer>
                </x-admin::drawer>
            </div>
        </div>

        <div class="flex flex-wrap gap-4">
            <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900" style="flex: 1; min-width: 220px;">
                <p class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <span class="icon-user text-base"></span>
                    @lang('omicslogic::app.reports.leads-in-range')
                </p>
                <p class="mt-1 text-2xl font-bold dark:text-white">{{ number_format($summary['leads_in_range']) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $summary['range_label'] }}</p>
            </div>

            <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900" style="flex: 1; min-width: 220px;">
                <p class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <span class="icon-activity text-base"></span>
                    @lang('omicslogic::app.reports.engaged-rate')
                </p>
                <p class="mt-1 text-2xl font-bold dark:text-white">{{ $summary['engaged_rate'] }}%</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('omicslogic::app.reports.engaged-learners', ['count' => number_format($summary['engaged'])])
                </p>
            </div>

            <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900" style="flex: 1; min-width: 220px;">
                <p class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <span class="icon-leads text-base"></span>
                    @lang('omicslogic::app.reports.conversion-rate')
                </p>
                <p class="mt-1 text-2xl font-bold dark:text-white">{{ $summary['conversion_rate'] }}%</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('omicslogic::app.reports.customer-count', ['count' => number_format($summary['customers'])])
                </p>
            </div>

            <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900" style="flex: 1; min-width: 220px;">
                <p class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <span class="icon-note text-base"></span>
                    @lang('omicslogic::app.reports.avg-per-month')
                </p>
                <p class="mt-1 text-2xl font-bold dark:text-white">{{ number_format($summary['avg_per_month']) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('omicslogic::app.reports.months-in-range', ['count' => $summary['months_in_range']])
                </p>
            </div>
        </div>

        @include('admin::omics.partials.report-month-chart', [
            'title' => trans('omicslogic::app.reports.by-month'),
            'items' => $byMonthItems,
            'rangeLabel' => $summary['range_label'],
        ])

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @include('admin::omics.partials.report-breakdown', [
                'title' => trans('omicslogic::app.reports.by-organization'),
                'icon' => 'icon-organization',
                'badge' => trans('omicslogic::app.reports.top-8'),
                'items' => $report['by_organization'],
                'barColor' => '#0d9488',
            ])

            @include('admin::omics.partials.report-breakdown', [
                'title' => trans('omicslogic::app.reports.by-education'),
                'icon' => 'icon-user',
                'badge' => trans('omicslogic::app.reports.background'),
                'items' => $report['by_education'],
                'barColor' => '#2563eb',
            ])

            @include('admin::omics.partials.report-breakdown', [
                'title' => trans('omicslogic::app.reports.by-source'),
                'icon' => 'icon-settings-webhooks',
                'items' => $report['by_source'],
                'barColor' => '#2563eb',
            ])

            @include('admin::omics.partials.report-funnel', [
                'items' => $report['funnel'],
                'total' => $summary['leads_in_range'],
                'engaged' => $summary['engaged'],
                'customers' => $summary['customers'],
            ])

            @include('admin::omics.partials.report-breakdown', [
                'title' => trans('omicslogic::app.reports.by-program'),
                'icon' => 'icon-product',
                'items' => $byProgramItems,
                'barColor' => '#9333ea',
            ])

            @include('admin::omics.partials.report-breakdown', [
                'title' => trans('omicslogic::app.reports.by-country'),
                'icon' => 'icon-location',
                'items' => $countryItems,
                'barColor' => '#2563eb',
            ])
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @include('admin::omics.partials.report-breakdown', [
                'title' => trans('omicslogic::app.reports.engaged-by-program'),
                'icon' => 'icon-activity',
                'badge' => trans('omicslogic::app.reports.most-finishes'),
                'items' => $engagedByProgramItems,
                'barColor' => '#9333ea',
            ])

            @include('admin::omics.partials.report-breakdown', [
                'title' => trans('omicslogic::app.reports.program-completion'),
                'icon' => 'icon-stats-up',
                'badge' => trans('omicslogic::app.reports.who-finish'),
                'items' => $programCompletionItems,
                'colorFromRate' => true,
                'footer' => trans('omicslogic::app.reports.completion-footnote'),
            ])
        </div>

        @include('admin::omics.partials.report-yoy-chart', [
            'title' => trans('omicslogic::app.reports.yoy-total'),
            'items' => $report['yoy'],
            'selectedYear' => $filters['year'],
            'footer' => trans('omicslogic::app.reports.yoy-footnote', ['year' => now()->year]),
        ])

        {{-- Export button and Scenario text removed to match system reports design --}}
    </div>
</x-admin::layouts>
