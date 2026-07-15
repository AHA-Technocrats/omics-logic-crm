@php
    $segment = $segment ?? null;
    $filters = $segment?->filter_query ?? [];
    $sources = app(\AHATechnocrats\Lead\Repositories\SourceRepository::class)->all(['id', 'name']);
    $campaigns = app(\AHATechnocrats\Product\Repositories\ProductRepository::class)->all(['id', 'name']);
    $owners = app(\AHATechnocrats\User\Repositories\UserRepository::class)->all(['id', 'name']);
    $countries = config('omicslogic.countries', []);
    $educationLevels = ['Undergraduate', 'Masters', 'PhD', 'Faculty', 'Industry'];
@endphp

<div class="grid grid-cols-1 gap-4">
    <x-admin::form.control-group>
        <x-admin::form.control-group.label class="required">
            @lang('omicslogic::app.segments.name')
        </x-admin::form.control-group.label>
        <x-admin::form.control-group.control
            type="text"
            name="name"
            rules="required"
            :value="old('name', $segment?->name)"
        />
    </x-admin::form.control-group>

    <x-admin::form.control-group>
        <x-admin::form.control-group.label>
            @lang('omicslogic::app.fields.description')
        </x-admin::form.control-group.label>
        <x-admin::form.control-group.control
            type="textarea"
            name="description"
            :value="old('description', $segment?->description)"
        />
    </x-admin::form.control-group>

    <p class="text-sm font-semibold text-gray-800 dark:text-white">@lang('omicslogic::app.segments.filter-rules')</p>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <x-admin::form.control-group>
            <x-admin::form.control-group.label>@lang('omicslogic::app.fields.country')</x-admin::form.control-group.label>
            <x-admin::form.control-group.control type="select" name="filter_country_code">
                <option value="">@lang('omicslogic::app.fields.any')</option>
                @foreach ($countries as $country)
                    <option value="{{ $country }}" @selected(old('filter_country_code', $filters['country_code'] ?? '') === $country)>{{ $country }}</option>
                @endforeach
            </x-admin::form.control-group.control>
        </x-admin::form.control-group>


        <x-admin::form.control-group>
            <x-admin::form.control-group.label>@lang('omicslogic::app.fields.campaign')</x-admin::form.control-group.label>
            <x-admin::form.control-group.control type="select" name="filter_primary_product_id">
                <option value="">@lang('omicslogic::app.fields.any')</option>
                @foreach ($campaigns as $campaign)
                    <option value="{{ $campaign->id }}" @selected((string) old('filter_primary_product_id', $filters['primary_product_id'] ?? '') === (string) $campaign->id)>{{ $campaign->name }}</option>
                @endforeach
            </x-admin::form.control-group.control>
        </x-admin::form.control-group>

        <x-admin::form.control-group>
            <x-admin::form.control-group.label>@lang('omicslogic::app.fields.source')</x-admin::form.control-group.label>
            <x-admin::form.control-group.control type="select" name="filter_primary_source_id">
                <option value="">@lang('omicslogic::app.fields.any')</option>
                @foreach ($sources as $source)
                    <option value="{{ $source->id }}" @selected((string) old('filter_primary_source_id', $filters['primary_source_id'] ?? '') === (string) $source->id)>{{ $source->name }}</option>
                @endforeach
            </x-admin::form.control-group.control>
        </x-admin::form.control-group>

        <x-admin::form.control-group>
            <x-admin::form.control-group.label>@lang('omicslogic::app.fields.education')</x-admin::form.control-group.label>
            <x-admin::form.control-group.control type="select" name="filter_education_level">
                <option value="">@lang('omicslogic::app.fields.any')</option>
                @foreach ($educationLevels as $level)
                    <option value="{{ $level }}" @selected(old('filter_education_level', $filters['education_level'] ?? '') === $level)>{{ $level }}</option>
                @endforeach
            </x-admin::form.control-group.control>
        </x-admin::form.control-group>

        <x-admin::form.control-group>
            <x-admin::form.control-group.label>@lang('omicslogic::app.fields.engagement')</x-admin::form.control-group.label>
            <x-admin::form.control-group.control type="select" name="filter_engagement">
                <option value="">@lang('omicslogic::app.fields.any')</option>
                <option value="yes" @selected(old('filter_engagement', $filters['engagement'] ?? '') === 'yes')>@lang('omicslogic::app.fields.engagement-yes')</option>
                <option value="no" @selected(old('filter_engagement', $filters['engagement'] ?? '') === 'no')>@lang('omicslogic::app.fields.engagement-no')</option>
            </x-admin::form.control-group.control>
        </x-admin::form.control-group>

        <x-admin::form.control-group>
            <x-admin::form.control-group.label>@lang('omicslogic::app.fields.owner')</x-admin::form.control-group.label>
            <x-admin::form.control-group.control type="select" name="filter_user_id">
                <option value="">@lang('omicslogic::app.fields.any')</option>
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((string) old('filter_user_id', $filters['user_id'] ?? '') === (string) $owner->id)>{{ $owner->name }}</option>
                @endforeach
            </x-admin::form.control-group.control>
        </x-admin::form.control-group>

        <x-admin::form.control-group>
            <x-admin::form.control-group.label>@lang('omicslogic::app.segments.refresh')</x-admin::form.control-group.label>
            <x-admin::form.control-group.control type="select" name="refresh_schedule">
                <option value="manual" @selected(old('refresh_schedule', $segment?->refresh_schedule ?? 'manual') === 'manual')>@lang('omicslogic::app.segments.refresh-manual')</option>
                <option value="daily" @selected(old('refresh_schedule', $segment?->refresh_schedule) === 'daily')>@lang('omicslogic::app.segments.refresh-daily')</option>
                <option value="weekly" @selected(old('refresh_schedule', $segment?->refresh_schedule) === 'weekly')>@lang('omicslogic::app.segments.refresh-weekly')</option>
            </x-admin::form.control-group.control>
        </x-admin::form.control-group>

        <x-admin::form.control-group>
            <x-admin::form.control-group.label>@lang('omicslogic::app.fields.owner')</x-admin::form.control-group.label>
            <x-admin::form.control-group.control type="select" name="owner_id">
                <option value="">@lang('omicslogic::app.fields.unassigned')</option>
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((string) old('owner_id', $segment?->owner_id ?? auth()->guard('user')->id()) === (string) $owner->id)>{{ $owner->name }}</option>
                @endforeach
            </x-admin::form.control-group.control>
        </x-admin::form.control-group>

        <x-admin::form.control-group class="flex items-center gap-2">
            <x-admin::form.control-group.control
                type="checkbox"
                name="is_shared"
                value="1"
                :checked="(bool) old('is_shared', $segment?->is_shared ?? false)"
            />
            <x-admin::form.control-group.label class="!mb-0">@lang('omicslogic::app.segments.shared')</x-admin::form.control-group.label>
        </x-admin::form.control-group>
    </div>
</div>
