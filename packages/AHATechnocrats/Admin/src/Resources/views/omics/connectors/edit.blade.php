<x-admin::layouts>
    <x-slot:title>
        @lang('omicslogic::app.connectors.configure') — {{ $connector->name }}
    </x-slot>

    @php
        $config = $connector->config ?? [];
    @endphp

    <x-admin::form :action="route('admin.omics.connectors.update', $connector->id)" method="PUT">
        <div class="flex flex-col gap-4">
            <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div>
                    <x-admin::breadcrumbs name="omics.connectors.edit" :entity="$connector" />
                    <div class="text-xl font-bold dark:text-white">
                        {{ $connector->name }}
                    </div>
                </div>
                <button type="submit" class="primary-button">@lang('omicslogic::app.connectors.save-btn')</button>

                @if ($connector->type === 'portal_api')
                    <form
                        method="POST"
                        action="{{ route('admin.omics.connectors.reset-sync', $connector->id) }}"
                        class="inline"
                        onsubmit="return confirm(@json(__('omicslogic::app.connectors.reset-sync-confirm')));"
                    >
                        @csrf
                        <button type="submit" class="secondary-button">
                            @lang('omicslogic::app.connectors.reset-sync')
                        </button>
                    </form>
                @endif
            </div>

            <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('omicslogic::app.segments.name')
                    </x-admin::form.control-group.label>
                    <x-admin::form.control-group.control type="text" name="name" :value="old('name', $connector->name)" rules="required" />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('omicslogic::app.datagrid.status')
                    </x-admin::form.control-group.label>
                    <x-admin::form.control-group.control
                        type="select"
                        name="status"
                        :value="old('status', $connector->status)"
                    >
                        <option value="connected" @selected(old('status', $connector->status) === 'connected')>Connected</option>
                        <option value="disabled" @selected(old('status', $connector->status) === 'disabled')>Disabled</option>
                        <option value="error" @selected(old('status', $connector->status) === 'error')>Error</option>
                    </x-admin::form.control-group.control>
                </x-admin::form.control-group>

                @if ($connector->type === 'portal_api')
                    <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950">
                        <p class="text-sm font-semibold dark:text-white">
                            @lang('omicslogic::app.connectors.firebase-title')
                        </p>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                            @lang('omicslogic::app.connectors.firebase-help')
                        </p>

                        <dl class="mt-3 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">@lang('omicslogic::app.connectors.firebase-status')</dt>
                                <dd class="font-medium {{ $firebaseConfigured ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                                    {{ $firebaseConfigured ? __('omicslogic::app.connectors.firebase-connected') : __('omicslogic::app.connectors.firebase-missing') }}
                                </dd>
                            </div>

                            @if ($firebaseProjectId)
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">@lang('omicslogic::app.connectors.firebase-project')</dt>
                                    <dd class="font-medium dark:text-white">{{ $firebaseProjectId }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('omicslogic::app.connectors.web-form')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="select"
                            name="web_form_id"
                            :value="old('web_form_id', $config['web_form_id'] ?? '')"
                            rules="required"
                        >
                            <option value="">@lang('omicslogic::app.connectors.web-form-placeholder')</option>
                            @foreach ($webForms as $webForm)
                                <option
                                    value="{{ $webForm->id }}"
                                    @selected((string) old('web_form_id', $config['web_form_id'] ?? '') === (string) $webForm->id)
                                >
                                    {{ $webForm->title }}
                                </option>
                            @endforeach
                        </x-admin::form.control-group.control>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            @lang('omicslogic::app.connectors.web-form-help')
                        </p>
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('omicslogic::app.connectors.sync-schedule')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="select"
                            name="sync_schedule"
                            :value="old('sync_schedule', $config['sync_schedule'] ?? 'manual')"
                        >
                            @foreach (['manual' => 'schedule-manual', 'hourly' => 'schedule-hourly', 'daily' => 'schedule-daily', 'weekly' => 'schedule-weekly'] as $value => $labelKey)
                                <option value="{{ $value }}" @selected(old('sync_schedule', $config['sync_schedule'] ?? 'manual') === $value)>
                                    @lang('omicslogic::app.connectors.'.$labelKey)
                                </option>
                            @endforeach
                        </x-admin::form.control-group.control>
                    </x-admin::form.control-group>
                @endif
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>
