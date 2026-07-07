@php
    $record = $record ?? null;
    $categories = config('omicslogic.campaign_categories', []);
    $aliases = $aliases ?? ($record ? \DB::table('omics_product_aliases')->where('product_id', $record->id)->pluck('alias_name')->implode(', ') : '');
@endphp

<div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-800">
    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
        @lang('omicslogic::app.fields.campaign-profile')
    </p>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <x-admin::form.control-group>
            <x-admin::form.control-group.label>
                @lang('omicslogic::app.datagrid.category')
            </x-admin::form.control-group.label>
            <x-admin::form.control-group.control type="select" name="category" :value="old('category', $record?->category)">
                <option value="">@lang('omicslogic::app.fields.any')</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(old('category', $record?->category) === $category)>{{ $category }}</option>
                @endforeach
            </x-admin::form.control-group.control>
        </x-admin::form.control-group>

        <x-admin::form.control-group>
            <x-admin::form.control-group.label>
                @lang('omicslogic::app.datagrid.status')
            </x-admin::form.control-group.label>
            <x-admin::form.control-group.control type="select" name="mapping_status" :value="old('mapping_status', $record?->mapping_status ?? 'mapped')">
                <option value="mapped" @selected(old('mapping_status', $record?->mapping_status ?? 'mapped') === 'mapped')>@lang('omicslogic::app.fields.status-mapped')</option>
                <option value="review" @selected(old('mapping_status', $record?->mapping_status) === 'review')>@lang('omicslogic::app.fields.status-review')</option>
            </x-admin::form.control-group.control>
        </x-admin::form.control-group>

        <x-admin::form.control-group class="md:col-span-2">
            <x-admin::form.control-group.label>
                @lang('omicslogic::app.datagrid.aliases')
            </x-admin::form.control-group.label>
            <x-admin::form.control-group.control
                type="text"
                name="aliases"
                :value="old('aliases', $aliases)"
                placeholder="Genomics 101, Intro to Genomics, Genomics Workshop"
            />
            <x-admin::form.control-group.label class="!text-xs !text-gray-400 !font-normal">
                Enter comma-separated aliases for mapping incoming course names to this canonical campaign.
            </x-admin::form.control-group.label>
        </x-admin::form.control-group>

        <x-admin::form.control-group class="flex items-center gap-2">
            <x-admin::form.control-group.control
                type="checkbox"
                name="is_active"
                value="1"
                :checked="(bool) old('is_active', $record?->is_active ?? true)"
            />
            <x-admin::form.control-group.label class="!mb-0">
                @lang('omicslogic::app.fields.status-active')
            </x-admin::form.control-group.label>
        </x-admin::form.control-group>
    </div>
</div>
