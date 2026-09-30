@php
    $record = $record ?? null;
    $namePrefix = $namePrefix ?? null;
    $isNested = $isNested ?? false;
    $fieldName = fn (string $field) => $namePrefix ? "{$namePrefix}[{$field}]" : $field;
    $countries = config('omicslogic.countries', []);
    $types = \AHATechnocrats\OmicsLogic\Enums\OrganizationType::cases();
    $selectedType = old($fieldName('type'), $record?->type);
    $normalizedSelectedType = \AHATechnocrats\OmicsLogic\Enums\OrganizationType::tryFrom(strtolower((string) $selectedType))?->value
        ?? strtolower((string) $selectedType);
    $selectedCountry = old($fieldName('country_code'), $record?->country_code);
    $selectedWebsite = old($fieldName('website'), $record?->website);
    $selectedNotes = old($fieldName('notes'), $record?->notes);
    $showHeading = $showHeading ?? true;
    $showOrganizationLookup = $showOrganizationLookup ?? false;
@endphp

@if ($showHeading)
<div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-800">
    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
        @lang('omicslogic::app.fields.organization-profile')
    </p>
@else
<div>
@endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @if ($showOrganizationLookup)
            <!-- Person Organization Name Lookup -->
            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('admin::app.leads.common.contact.organization')
                </x-admin::form.control-group.label>

                @php
                    $organizationAttribute = app('AHATechnocrats\Attribute\Repositories\AttributeRepository')->findOneWhere([
                        'entity_type' => 'persons',
                        'code' => 'organization_id'
                    ]);

                    $organizationAttribute->code = 'person[' . $organizationAttribute->code . ']';
                @endphp

                <x-admin::attributes.edit.lookup />

                <v-lookup-component
                    :key="person?.organization?.id || 'org-lookup'"
                    :attribute='@json($organizationAttribute)'
                    :value="person?.organization"
                    :is-disabled="person?.id ? true : false"
                    can-add-new="true"
                    @lookup-added="handleOrganizationAdded"
                    @lookup-removed="handleOrganizationRemoved"
                ></v-lookup-component>

                <x-admin::form.control-group.control
                    type="hidden"
                    name="person[organization_name]"
                    v-model="organizationName"
                    v-if="organizationName"
                />
            </x-admin::form.control-group>
        @endif
        <x-admin::form.control-group>
            <x-admin::form.control-group.label>
                @lang('omicslogic::app.fields.type')
            </x-admin::form.control-group.label>
            <x-admin::form.control-group.control
                type="select"
                name="{{ $fieldName('type') }}"
                :value="$normalizedSelectedType"
                :v-model="$isNested ? 'person.organization.type' : null"
                ::disabled="$isNested ? 'person.id || person.organization?.id' : null"
            >
                <option value="">@lang('omicslogic::app.fields.any')</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected($normalizedSelectedType === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </x-admin::form.control-group.control>
        </x-admin::form.control-group>

        @unless ($isNested)
            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('omicslogic::app.fields.country')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control
                    type="select"
                    name="{{ $fieldName('country_code') }}"
                    :value="$selectedCountry"
                >
                    <option value="">@lang('omicslogic::app.fields.any')</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country }}" @selected($selectedCountry === $country)>{{ $country }}</option>
                    @endforeach
                </x-admin::form.control-group.control>
            </x-admin::form.control-group>
        @endunless

        <x-admin::form.control-group>
            <x-admin::form.control-group.label>
                @lang('omicslogic::app.fields.website')
            </x-admin::form.control-group.label>
            <x-admin::form.control-group.control
                type="text"
                name="{{ $fieldName('website') }}"
                :value="$selectedWebsite"
                :v-model="$isNested ? 'person.organization.website' : null"
                ::disabled="$isNested ? 'person.id || person.organization?.id' : null"
            />
        </x-admin::form.control-group>

        <x-admin::form.control-group class="md:col-span-2 xl:col-span-1">
            <x-admin::form.control-group.label>
                @lang('omicslogic::app.fields.notes')
            </x-admin::form.control-group.label>
            <x-admin::form.control-group.control
                type="textarea"
                name="{{ $fieldName('notes') }}"
                :value="$selectedNotes"
                :v-model="$isNested ? 'person.organization.notes' : null"
                ::disabled="$isNested ? 'person.id || person.organization?.id' : null"
            />
        </x-admin::form.control-group>
    </div>
</div>
