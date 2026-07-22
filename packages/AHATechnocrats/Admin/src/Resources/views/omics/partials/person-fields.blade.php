@php
    $record = $record ?? null;
    $namePrefix = $namePrefix ?? null;
    $isNested = $isNested ?? false;
    $fieldName = fn (string $field) => $namePrefix ? "{$namePrefix}[{$field}]" : $field;
    $campaigns = app(\AHATechnocrats\Product\Repositories\ProductRepository::class)->all(['id', 'name']);
    $sources = app(\AHATechnocrats\Lead\Repositories\SourceRepository::class)->all(['id', 'name']);
    $owners = app(\AHATechnocrats\User\Repositories\UserRepository::class)->all(['id', 'name', 'image']);
    $countries = config('omicslogic.countries', []);
    $educationLevels = ['Undergraduate', 'Masters', 'PhD', 'Faculty', 'Industry'];
    $selectedCountry = old(
        $fieldName('country_code'),
        $record?->organization?->country_code ?: $record?->country_code,
    );
    $selectedEducation = old($fieldName('education_level'), $record?->education_level);
    $selectedProduct = old($fieldName('primary_product_id'), $record?->primary_product_id);
    $selectedSource = old($fieldName('primary_source_id'), $record?->primary_source_id);
    $selectedOwner = old($fieldName('user_id'), $record?->user_id);
    $selectedOwnerUser = $record?->user ?? $owners->firstWhere('id', (int) $selectedOwner);
    $ownerProfileImages = $selectedOwnerUser?->image
        ? [['id' => 'image', 'url' => $selectedOwnerUser->image_url]]
        : [];
    $selectedInquiry = old($fieldName('inquiry_details'), $record?->inquiry_details);
    $showHeading = $showHeading ?? true;
    $showCrmFields = $showCrmFields ?? true;
    $showContactFields = $showContactFields ?? true;
    $showInquiryField = $showInquiryField ?? true;
@endphp

@if ($showHeading)
<div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-800">
    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
        @lang('omicslogic::app.fields.contact-profile')
    </p>
@else
<div>
@endif

    @if ($showContactFields)
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('omicslogic::app.fields.country')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control
                    type="select"
                    :name="$fieldName('country_code')"
                    :value="$selectedCountry"
                    :v-model="$isNested ? 'person.country_code' : null"
                    ::disabled="$isNested ? 'person.id ? true : false' : null"
                >
                    <option value="">@lang('omicslogic::app.fields.any')</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country }}" @selected($selectedCountry === $country)>{{ $country }}</option>
                    @endforeach
                </x-admin::form.control-group.control>
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('omicslogic::app.fields.education')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control
                    type="select"
                    :name="$fieldName('education_level')"
                    :value="$selectedEducation"
                    :v-model="$isNested ? 'person.education_level' : null"
                    ::disabled="$isNested ? 'person.id ? true : false' : null"
                >
                    <option value="">@lang('omicslogic::app.fields.any')</option>
                    @foreach ($educationLevels as $level)
                        <option value="{{ $level }}" @selected($selectedEducation === $level)>{{ $level }}</option>
                    @endforeach
                </x-admin::form.control-group.control>
            </x-admin::form.control-group>

            @if ($showInquiryField)
            <x-admin::form.control-group class="md:col-span-2">
                <x-admin::form.control-group.label>
                    @lang('omicslogic::app.fields.inquiry-details')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control
                    type="textarea"
                    :name="$fieldName('inquiry_details')"
                    :value="$selectedInquiry"
                    :v-model="$isNested ? 'person.inquiry_details' : null"
                    ::disabled="$isNested ? 'person.id ? true : false' : null"
                />
            </x-admin::form.control-group>
            @endif
        </div>
    @endif

    @if ($showCrmFields)
        <div @class([
            'mt-4 grid grid-cols-1 gap-4 md:grid-cols-2',
            'border-t border-gray-200 pt-4 dark:border-gray-800' => $showContactFields,
        ])>
            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('omicslogic::app.fields.campaign')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control
                    type="select"
                    :name="$fieldName('primary_product_id')"
                    :value="$selectedProduct"
                    :v-model="$isNested ? 'person.primary_product_id' : null"
                    ::disabled="$isNested ? 'person.id ? true : false' : null"
                >
                    <option value="">@lang('omicslogic::app.fields.any')</option>
                    @foreach ($campaigns as $campaign)
                        <option value="{{ $campaign->id }}" @selected((string) $selectedProduct === (string) $campaign->id)>{{ $campaign->name }}</option>
                    @endforeach
                </x-admin::form.control-group.control>
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('omicslogic::app.fields.source')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control
                    type="select"
                    :name="$fieldName('primary_source_id')"
                    :value="$selectedSource"
                    :v-model="$isNested ? 'person.primary_source_id' : null"
                    ::disabled="$isNested ? 'person.id ? true : false' : null"
                >
                    <option value="">@lang('omicslogic::app.fields.any')</option>
                    @foreach ($sources as $source)
                        <option value="{{ $source->id }}" @selected((string) $selectedSource === (string) $source->id)>{{ $source->name }}</option>
                    @endforeach
                </x-admin::form.control-group.control>
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('omicslogic::app.fields.owner')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control
                    type="select"
                    :name="$fieldName('user_id')"
                    :value="$selectedOwner"
                    :v-model="$isNested ? 'person.user_id' : null"
                    ::disabled="$isNested ? 'person.id ? true : false' : null"
                >
                    <option value="">@lang('omicslogic::app.fields.unassigned')</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}" @selected((string) $selectedOwner === (string) $owner->id)>{{ $owner->name }}</option>
                    @endforeach
                </x-admin::form.control-group.control>
            </x-admin::form.control-group>


        </div>
    @endif
</div>
