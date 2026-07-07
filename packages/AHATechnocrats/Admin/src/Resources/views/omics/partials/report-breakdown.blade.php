@props([
    'title',
    'items',
    'icon' => 'icon-stats-up',
    'badge' => null,
    'emptyMessage' => null,
    'colorFromRate' => false,
    'barColor' => '#2563eb',
    'footer' => null,
])

@php
    $collection = collect($items);
    $max = $colorFromRate ? 100 : ($collection->max('total') ?: 1);
    $palette = ['#2563eb', '#9333ea', '#0d9488', '#16a34a', '#f59e0b'];
@endphp

@include('admin::omics.partials.report-charts-styles')

<div class="rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
        <h3 class="flex items-center gap-2 font-semibold dark:text-white">
            <span class="{{ $icon }} text-xl"></span>
            {{ $title }}
        </h3>

        @if ($badge)
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $badge }}</span>
        @endif
    </div>

    <div class="p-4">
        @if ($collection->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $emptyMessage ?? __('omicslogic::app.reports.no-data') }}
            </p>
        @else
            <div class="omics-report-bars">
                @foreach ($collection as $index => $item)
                    @php
                        $label = is_array($item) ? ($item['label'] ?? '—') : $item;
                        $total = is_array($item) ? ($item['total'] ?? 0) : 0;
                        $width = $max > 0 && $total > 0 ? max(4, round(($total / $max) * 100)) : 0;

                        if ($colorFromRate) {
                            $fillColor = $total >= 60 ? '#16a34a' : ($total >= 50 ? '#f59e0b' : '#9ca3af');
                        } else {
                            $fillColor = $barColor ?: $palette[$index % count($palette)];
                        }
                    @endphp

                    <div
                        class="omics-bar-row"
                        style="display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 2fr) 3rem; align-items: center; gap: 12px;"
                    >
                        <span class="omics-bar-label dark:text-white" title="{{ $label }}">{{ $label }}</span>

                        <div class="omics-bar-track" style="height: 10px; width: 100%; border-radius: 9999px; background: #e5e7eb; overflow: hidden;">
                            <div
                                class="omics-bar-fill"
                                style="width: {{ $width }}%; height: 10px; border-radius: 9999px; background-color: {{ $fillColor }};"
                            ></div>
                        </div>

                        <span class="omics-bar-value">
                            {{ $colorFromRate ? $total.'%' : number_format($total) }}
                        </span>
                    </div>
                @endforeach
            </div>

            @if ($footer)
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">{{ $footer }}</p>
            @endif
        @endif
    </div>
</div>
