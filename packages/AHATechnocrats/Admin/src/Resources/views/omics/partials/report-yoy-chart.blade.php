@props(['title', 'items', 'selectedYear' => null, 'footer' => null])

@php
    $collection = collect($items);
    $max = $collection->max('total') ?: 1;
@endphp

@include('admin::omics.partials.report-charts-styles')

<div class="rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
        <h3 class="flex items-center gap-2 font-semibold dark:text-white">
            <span class="icon-stats-up text-xl"></span>
            {{ $title }}
        </h3>

        <span class="text-xs text-gray-500 dark:text-gray-400">@lang('omicslogic::app.reports.all-sources')</span>
    </div>

    <div class="p-4">
        @if ($collection->isEmpty())
            <p class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                @lang('omicslogic::app.reports.no-data')
            </p>
        @else
            <div
                style="display: flex; flex-direction: row; align-items: flex-end; justify-content: space-around; height: 170px; gap: 20px; width: 100%; overflow-x: auto; box-sizing: border-box; padding: 8px 8px 0;"
            >
                @foreach ($collection as $item)
                    @php
                        $isSelected = ($item['selected'] ?? false) || ($selectedYear && (string) $item['label'] === (string) $selectedYear);
                        $height = $item['total'] > 0 ? max(8, round(($item['total'] / $max) * 120)) : 4;
                        $barColor = $isSelected ? 'var(--brand-color)' : '#bfdbfe';
                        $textColor = $isSelected ? 'var(--brand-color)' : '#6b7280';
                    @endphp

                    <div style="display: flex; flex: 1 1 0; flex-direction: column; align-items: center; justify-content: flex-end; min-width: 56px; max-width: 90px; height: 100%; gap: 7px;">
                        <span style="font-size: {{ $isSelected ? '13px' : '12px' }}; font-weight: 700; color: {{ $textColor }};">
                            {{ number_format($item['total']) }}
                        </span>

                        <div style="width: 100%; max-width: 70px; height: {{ $height }}px; background-color: {{ $barColor }}; border-radius: 6px 6px 0 0;"></div>

                        <span style="font-size: 12px; font-weight: {{ $isSelected ? '700' : '400' }}; color: {{ $textColor }};">
                            {{ $item['label'] }}
                        </span>
                    </div>
                @endforeach
            </div>

            @if ($footer)
                <p class="mt-2 px-2 text-xs text-gray-500 dark:text-gray-400">{{ $footer }}</p>
            @endif
        @endif
    </div>
</div>
