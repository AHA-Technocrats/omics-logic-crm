@props(['items', 'total' => 0, 'leads' => 0, 'engaged' => 0, 'customers' => 0])

@php
    $steps = collect($items);
    $colors = ['#6b7280', '#2563eb', '#9333ea', '#16a34a'];
    $widths = [100, 85, 65, 48];
    $contactToLead = $total > 0 ? round(($leads / $total) * 100) : 0;
    $leadToEngaged = $leads > 0 ? round(($engaged / $leads) * 100) : 0;
    $engagedToCustomer = $engaged > 0 ? round(($customers / $engaged) * 100) : 0;
@endphp

@include('admin::omics.partials.report-charts-styles')

<div class="rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
        <h3 class="flex items-center gap-2 font-semibold dark:text-white">
            <span class="icon-leads text-xl"></span>
            @lang('omicslogic::app.reports.funnel')
        </h3>
    </div>

    <div class="p-4">
        @if ($steps->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('omicslogic::app.reports.no-data')</p>
        @else
            <div class="omics-funnel" style="display: flex; flex-direction: column; align-items: center; gap: 8px; width: 100%; max-width: 32rem; margin: 0 auto;">
                @foreach ($steps as $index => $step)
                    @php
                        $isEmpty = ($step['total'] ?? 0) <= 0;
                    @endphp

                    <div
                        @class(['omics-funnel-step', 'is-empty' => $isEmpty])
                        style="width: {{ $widths[$index] ?? 48 }}%; background-color: {{ $colors[$index] ?? '#6b7280' }}; border-radius: 6px; padding: 10px 16px; text-align: center; font-size: 13px; font-weight: 600; color: #fff; min-height: 40px; display: flex; align-items: center; justify-content: center; opacity: {{ $isEmpty ? '0.45' : '1' }};"
                    >
                        {{ $step['label'] }} · {{ number_format($step['total']) }}
                    </div>
                @endforeach
            </div>

            <p class="omics-funnel-caption">
                @lang('omicslogic::app.reports.funnel-caption', [
                    'leadToEngaged' => $leadToEngaged,
                    'engagedToCustomer' => $engagedToCustomer,
                ])
            </p>
        @endif
    </div>
</div>
