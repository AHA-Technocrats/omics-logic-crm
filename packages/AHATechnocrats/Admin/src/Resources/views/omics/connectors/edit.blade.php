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
                
                <div class="flex items-center gap-x-4">
                    @if ($connector->type === 'portal_api')
                        <v-reset-sync></v-reset-sync>
                    @endif

                    <button type="submit" class="primary-button">@lang('omicslogic::app.connectors.save-btn')</button>
                </div>
            </div>

            <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('omicslogic::app.connectors.general')
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        @lang('omicslogic::app.connectors.general-info')
                    </p>
                </div>
                
                @if ($connector->type === 'portal_api')
                    <div class="mb-6 rounded-lg bg-blue-50 p-4 border border-blue-100 dark:bg-blue-900/20 dark:border-blue-800">
                        <div class="flex items-start">
                            <span class="icon-info text-2xl text-blue-600 dark:text-blue-400 mt-0.5 mr-3"></span>
                            <div>
                                <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-1">Background Synchronization</h3>
                                <p class="text-sm text-blue-700 dark:text-blue-400">
                                    The OmicsLogic Portal sync runs automatically in the background via a scheduled task.
                                    Any changes you make here will apply to the next automatic sync. You do not need to click Sync manually unless you want to force an immediate import.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <x-admin::form.control-group class="mb-0">
                        <x-admin::form.control-group.label class="required">
                            @lang('omicslogic::app.segments.name')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="name" :value="old('name', $connector->name)" rules="required" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group class="mb-0">
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
                        <x-admin::form.control-group class="mb-0">
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

                        <x-admin::form.control-group class="mb-0">
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

                        <x-admin::form.control-group class="mb-0">
                            <x-admin::form.control-group.label>
                                Initial Sync Date (Optional)
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="date"
                                name="sync_from_date"
                                :value="old('sync_from_date', $config['sync_from_date'] ?? '')"
                                placeholder="YYYY-MM-DD"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Only used for the very first sync.
                            </p>
                        </x-admin::form.control-group>
                    @endif
                </div>
            </div>
        </div>
    </x-admin::form>

    @if ($connector->type === 'portal_api')
        @pushOnce('scripts')
            <script type="text/x-template" id="v-reset-sync-template">
                <form
                    id="reset-sync-form"
                    method="POST"
                    action="{{ route('admin.omics.connectors.reset-sync', $connector->id) }}"
                    class="inline"
                >
                    @csrf
                    <button 
                        type="button" 
                        class="secondary-button text-red-600 border-red-200 hover:bg-red-50 dark:text-red-400 dark:border-red-800 dark:hover:bg-gray-800"
                        @click="confirmReset"
                    >
                        @lang('omicslogic::app.connectors.reset-sync')
                    </button>
                </form>
            </script>
            
            <script type="module">
                app.component('v-reset-sync', {
                    template: '#v-reset-sync-template',
                    methods: {
                        confirmReset() {
                            this.$emitter.emit('open-confirm-modal', {
                                title: 'Reset Sync State',
                                message: 'Warning: This will clear all previously synced data. All leads and persons imported via Firebase will be deleted, the last sync timestamp will be cleared, and pending merge reviews will be removed. Are you sure you want to proceed?',
                                options: {
                                    btnDisagree: 'Cancel',
                                    btnAgree: 'Reset Now',
                                },
                                agree: () => {
                                    document.getElementById('reset-sync-form').submit();
                                }
                            });
                        }
                    }
                });
            </script>
        @endPushOnce
    @endif
</x-admin::layouts>
