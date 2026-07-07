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
                    <x-admin::form.control-group.control type="select" name="status">
                        <option value="connected" @selected(old('status', $connector->status) === 'connected')>Connected</option>
                        <option value="disabled" @selected(old('status', $connector->status) === 'disabled')>Disabled</option>
                        <option value="error" @selected(old('status', $connector->status) === 'error')>Error</option>
                    </x-admin::form.control-group.control>
                </x-admin::form.control-group>

                @if ($connector->type === 'portal_api')
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('omicslogic::app.connectors.api-url')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="api_url" :value="old('api_url', $config['api_url'] ?? '')" rules="required|url" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('omicslogic::app.connectors.api-token')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="password" name="api_token" :value="old('api_token', $config['api_token'] ?? '')" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('omicslogic::app.connectors.sync-schedule')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="select" name="sync_schedule">
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
