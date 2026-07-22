<x-admin::layouts>
    <x-slot:title>
        @lang('omicslogic::app.connectors.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div>
                <x-admin::breadcrumbs name="omics.connectors" />
                <div class="text-xl font-bold dark:text-white">
                    @lang('omicslogic::app.connectors.title')
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    @lang('omicslogic::app.connectors.subtitle')
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach ($connectors as $connector)
                @php
                    $statusClass = match ($connector->status) {
                        'connected' => 'bg-green-100 text-green-700',
                        'error' => 'bg-red-100 text-red-700',
                        default => 'bg-amber-100 text-amber-700',
                    };
                @endphp

                <div class="rounded-lg border border-gray-300 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-base font-semibold dark:text-white">{{ $connector->name }}</h3>
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusClass }}">
                            {{ ucfirst(str_replace('_', ' ', $connector->status)) }}
                        </span>
                    </div>

                    <p class="mb-2 text-sm text-gray-600 dark:text-gray-300">
                        @lang('omicslogic::app.connectors.types.'.$connector->type)
                    </p>

                    <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                        @lang('omicslogic::app.connectors.last-sync'):
                        {{ $connector->last_sync_at ? core()->formatDate($connector->last_sync_at, 'd M Y H:i') : trans('omicslogic::app.connectors.never-synced') }}
                        @if ($connector->last_sync_status)
                            ({{ $connector->last_sync_status }})
                        @endif
                    </p>

                    <div class="flex flex-wrap gap-2">
                        @if ($connector->type === 'web_form')
                            <a href="{{ route('admin.web_forms.index') }}" class="secondary-button">
                                @lang('omicslogic::app.connectors.manage-forms')
                            </a>
                        @elseif ($connector->type === 'csv_import')
                            <a href="{{ route('admin.settings.data_transfer.imports.index') }}" class="secondary-button">
                                @lang('omicslogic::app.connectors.manage-imports')
                            </a>
                        @else
                            <a href="{{ route('admin.omics.connectors.edit', $connector->id) }}" class="secondary-button">
                                @lang('omicslogic::app.connectors.configure')
                            </a>
                        @endif

                        @if ($connector->type === 'portal_api')
                            <button type="button" class="secondary-button" @click="$refs.logsModal.toggle()">
                                Logs
                            </button>
                            <form method="POST" action="{{ route('admin.omics.connectors.sync', $connector->id) }}">
                                @csrf
                                <button type="submit" class="primary-button">
                                    @lang('omicslogic::app.connectors.sync')
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    <!-- Logs Modal -->
    <x-admin::modal ref="logsModal" size="medium">
        <x-slot:header>
            <h3 class="text-base font-semibold dark:text-white">
                @lang('omicslogic::app.connectors.recent-runs')
            </h3>
        </x-slot>

        <x-slot:content>
            <x-admin::datagrid src="{{ route('admin.omics.connectors.logs') }}" />
        </x-slot>
    </x-admin::modal>
</x-admin::layouts>
