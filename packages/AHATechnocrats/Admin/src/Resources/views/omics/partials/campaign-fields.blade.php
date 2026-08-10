@php
    $record = $record ?? null;
    $categories = app(\AHATechnocrats\Product\Repositories\CampaignCategoryRepository::class)
        ->getModel()
        ->newQuery()
        ->orderBy('name')
        ->get(['id', 'name']);
    $selectedCategoryId = old('category_id', $record?->category_id);
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
            <x-admin::form.control-group.control
                type="select"
                name="category_id"
                :value="old('category_id', $selectedCategoryId)"
            >
                <option value="">@lang('omicslogic::app.fields.any')</option>
                @foreach ($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        @selected((string) $selectedCategoryId === (string) $category->id)
                    >{{ $category->name }}</option>
                @endforeach
            </x-admin::form.control-group.control>
            <x-admin::form.control-group.error control-name="category_id" />
        </x-admin::form.control-group>

        <x-admin::form.control-group>
            <x-admin::form.control-group.label>
                @lang('omicslogic::app.fields.product-interest-score')
            </x-admin::form.control-group.label>
            @php
                $scorePresets = [35, 30, 25, 20, 15, 10, 5];
                $currentScore = (int) old('product_interest_score', $record?->product_interest_score ?? 5);
                if ($currentScore < 0) {
                    $currentScore = 0;
                }
                if ($currentScore > 35) {
                    $currentScore = 35;
                }
            @endphp
            <x-admin::form.control-group.control
                type="select"
                name="product_interest_score"
                :value="(string) $currentScore"
            >
                @if (! in_array($currentScore, $scorePresets, true))
                    <option value="{{ $currentScore }}" selected>{{ $currentScore }}</option>
                @endif
                @foreach ($scorePresets as $preset)
                    <option value="{{ $preset }}" @selected($currentScore === $preset)>{{ $preset }}</option>
                @endforeach
            </x-admin::form.control-group.control>
            <x-admin::form.control-group.label class="!text-xs !text-gray-400 !font-normal">
                @lang('omicslogic::app.fields.product-interest-score-help')
            </x-admin::form.control-group.label>
            <x-admin::form.control-group.error control-name="product_interest_score" />
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
