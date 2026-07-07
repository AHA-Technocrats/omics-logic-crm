@php
    $before = is_array($log->before) ? $log->before : [];
    $after = is_array($log->after) ? $log->after : [];
    $fieldKeys = collect(array_keys($before))->merge(array_keys($after))->unique()->values();

    $actionClass = match ($log->action) {
        'created' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
        'updated', 'connector_configure' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100',
        'deleted' => 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-100',
        'merge_contacts' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100',
        'undo' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-100',
        default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200',
    };

    $render = function ($value) {
        if (is_null($value)) {
            return '<span class="text-gray-400">—</span>';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return '<pre class="whitespace-pre-wrap break-words text-xs">'.e(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)).'</pre>';
        }

        return e((string) $value);
    };

    $humanize = fn (string $key) => \Illuminate\Support\Str::of(preg_replace('/_id$/', '', $key) ?: $key)
        ->replace('_', ' ')->title()->toString();
@endphp

<x-admin::layouts>
    <x-slot:title>
        @lang('omicslogic::app.audit.detail.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <x-admin::breadcrumbs name="omics.audit.view" :entity="$log" />

            <div class="flex items-center justify-between gap-4">
                <div class="text-xl font-bold dark:text-white">
                    @lang('omicslogic::app.audit.detail.title') #{{ $log->id }}
                </div>

                <a
                    href="{{ route('admin.omics.audit.index') }}"
                    class="primary-button"
                >
                    @lang('omicslogic::app.audit.detail.back')
                </a>
            </div>
        </div>

        {{-- Metadata --}}
        <div class="box-shadow rounded-lg bg-white p-4 dark:bg-gray-900">
            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                @lang('omicslogic::app.audit.detail.summary')
            </p>

            @if ($log->description)
                <p class="mb-4 text-gray-700 dark:text-gray-300">{{ $log->description }}</p>
            @endif

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <span class="block text-xs uppercase text-gray-400">@lang('omicslogic::app.audit.when')</span>
                    <span class="text-gray-800 dark:text-gray-200">{{ core()->formatDate($log->created_at, 'd M Y H:i:s') }}</span>
                </div>

                <div>
                    <span class="block text-xs uppercase text-gray-400">@lang('omicslogic::app.audit.actor')</span>
                    <span class="text-gray-800 dark:text-gray-200">
                        {{ optional($log->actor())->name
                            ?? ($log->actor_type === 'system'
                                ? trans('omicslogic::app.audit.system')
                                : trans('omicslogic::app.fields.unassigned')) }}
                    </span>
                </div>

                <div>
                    <span class="block text-xs uppercase text-gray-400">@lang('omicslogic::app.audit.action')</span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $actionClass }}">
                        {{ trans('omicslogic::app.audit.actions.'.$log->action) === 'omicslogic::app.audit.actions.'.$log->action
                            ? \Illuminate\Support\Str::headline($log->action)
                            : trans('omicslogic::app.audit.actions.'.$log->action) }}
                    </span>
                </div>

                <div>
                    <span class="block text-xs uppercase text-gray-400">@lang('omicslogic::app.audit.entity')</span>
                    <span class="text-gray-800 dark:text-gray-200">
                        {{ $log->entity_type ? \Illuminate\Support\Str::headline($log->entity_type) : '—' }}
                        @if ($log->entity_id)
                            <span class="text-gray-400">#{{ $log->entity_id }}</span>
                        @endif
                    </span>
                </div>

                <div>
                    <span class="block text-xs uppercase text-gray-400">@lang('omicslogic::app.audit.detail.route')</span>
                    <span class="text-gray-800 dark:text-gray-200">{{ $log->route ?? '—' }}</span>
                </div>

                <div>
                    <span class="block text-xs uppercase text-gray-400">@lang('omicslogic::app.audit.detail.ip')</span>
                    <span class="text-gray-800 dark:text-gray-200">{{ $log->ip_address ?? '—' }}</span>
                </div>
            </div>

            @if ($log->user_agent)
                <div class="mt-4">
                    <span class="block text-xs uppercase text-gray-400">@lang('omicslogic::app.audit.detail.user-agent')</span>
                    <span class="break-words text-sm text-gray-600 dark:text-gray-400">{{ $log->user_agent }}</span>
                </div>
            @endif
        </div>

        {{-- Changes --}}
        <div class="box-shadow rounded-lg bg-white p-4 dark:bg-gray-900">
            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                @lang('omicslogic::app.audit.detail.changes')
            </p>

            @if ($fieldKeys->isEmpty())
                <p class="text-gray-500 dark:text-gray-400">@lang('omicslogic::app.audit.detail.no-changes')</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-xs uppercase text-gray-400 dark:border-gray-800">
                                <th class="px-3 py-2">@lang('omicslogic::app.audit.detail.field')</th>
                                <th class="px-3 py-2">@lang('omicslogic::app.audit.detail.old-value')</th>
                                <th class="px-3 py-2">@lang('omicslogic::app.audit.detail.new-value')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fieldKeys as $key)
                                <tr class="border-b border-gray-100 align-top dark:border-gray-800">
                                    <td class="px-3 py-2 font-medium text-gray-700 dark:text-gray-300">{{ $humanize($key) }}</td>
                                    <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{!! $render($before[$key] ?? null) !!}</td>
                                    <td class="px-3 py-2 text-gray-800 dark:text-gray-200">{!! $render($after[$key] ?? null) !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-admin::layouts>
