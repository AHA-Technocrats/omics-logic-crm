@props(['title', 'items', 'rangeLabel' => null])

@php
    $collection = collect($items);
    $max = $collection->max(fn ($item) => is_array($item) ? ($item['total'] ?? 0) : $item) ?: 1;
@endphp

@include('admin::omics.partials.report-charts-styles')

<div class="rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
        <h3 class="flex items-center gap-2 font-semibold dark:text-white">
            <span class="icon-stats-up text-xl"></span>
            {{ $title }}
        </h3>

        @if ($rangeLabel)
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $rangeLabel }}</span>
        @endif
    </div>

    <div class="p-4">
        @if ($collection->isEmpty())
            <p class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                @lang('omicslogic::app.reports.no-data')
            </p>
        @else
            <div
                style="display: flex; flex-direction: row; align-items: flex-end; justify-content: space-between; height: 170px; gap: 12px; width: 100%; overflow-x: auto; box-sizing: border-box; padding: 8px 4px 0;"
            >
                @foreach ($collection as $item)
                    @php
                        $label = is_array($item) ? ($item['label'] ?? '—') : $item;
                        $total = is_array($item) ? ($item['total'] ?? 0) : (int) $item;
                        $height = $total > 0 ? max(8, round(($total / $max) * 120)) : 4;
                    @endphp

                    <div style="display: flex; flex: 1 1 0; flex-direction: column; align-items: center; justify-content: flex-end; min-width: 40px; max-width: 72px; height: 100%; gap: 6px;">
                        <span style="font-size: 12px; font-weight: 700; color: var(--brand-color);">{{ number_format($total) }}</span>

                        <div style="width: 100%; max-width: 46px; height: {{ $height }}px; background-color: var(--brand-color); border-radius: 6px 6px 0 0;"></div>

                        <span style="font-size: 12px; color: #6b7280; text-align: center;">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
