@php
    $record = $record ?? null;
    $namePrefix = $namePrefix ?? null;
    $isNested = $isNested ?? false;
    $fieldName = fn (string $field) => $namePrefix ? "{$namePrefix}[{$field}]" : $field;
    $owners = app(\AHATechnocrats\User\Repositories\UserRepository::class)->all(['id', 'name', 'image']);
    $countries = config('omicslogic.countries', []);
    $types = \AHATechnocrats\OmicsLogic\Enums\OrganizationType::cases();
    $selectedType = old($fieldName('type'), $record?->type);
    $normalizedSelectedType = \AHATechnocrats\OmicsLogic\Enums\OrganizationType::tryFrom(strtolower((string) $selectedType))?->value
        ?? strtolower((string) $selectedType);
    $selectedCountry = old($fieldName('country_code'), $record?->country_code);
    $selectedOwner = old($fieldName('account_owner_id'), $record?->account_owner_id);
    $selectedWebsite = old($fieldName('website'), $record?->website);
    $selectedNotes = old($fieldName('notes'), $record?->notes);
    $selectedOwnerUser = $record?->accountOwner
        ?? $owners->firstWhere('id', (int) $selectedOwner);
    $ownerProfileImages = $selectedOwnerUser?->image
        ? [['id' => 'image', 'url' => $selectedOwnerUser->image_url]]
        : [];
@endphp

<div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-800">
    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
        @lang('omicslogic::app.fields.organization-profile')
    </p>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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

        <x-admin::form.control-group>
            <x-admin::form.control-group.label>
                @lang('omicslogic::app.fields.country')
            </x-admin::form.control-group.label>
            <x-admin::form.control-group.control
                type="select"
                name="{{ $fieldName('country_code') }}"
                :value="$selectedCountry"
                :v-model="$isNested ? 'person.organization.country_code' : null"
                ::disabled="$isNested ? 'person.id || person.organization?.id' : null"
            >
                <option value="">@lang('omicslogic::app.fields.any')</option>
                @foreach ($countries as $country)
                    <option value="{{ $country }}" @selected($selectedCountry === $country)>{{ $country }}</option>
                @endforeach
            </x-admin::form.control-group.control>
        </x-admin::form.control-group>

        <x-admin::form.control-group>
            <x-admin::form.control-group.label>
                @lang('omicslogic::app.fields.account-owner')
            </x-admin::form.control-group.label>
            <x-admin::form.control-group.control
                type="select"
                name="{{ $fieldName('account_owner_id') }}"
                :value="$selectedOwner"
                :v-model="$isNested ? 'person.organization.account_owner_id' : null"
                ::disabled="$isNested ? 'person.id || person.organization?.id' : null"
            >
                <option value="">@lang('omicslogic::app.fields.unassigned')</option>
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((string) $selectedOwner === (string) $owner->id)>{{ $owner->name }}</option>
                @endforeach
            </x-admin::form.control-group.control>
        </x-admin::form.control-group>

        @if (! $isNested)
            <x-admin::form.control-group class="md:col-span-2">
                <x-admin::form.control-group.label>
                    @lang('omicslogic::app.fields.owner-profile')
                </x-admin::form.control-group.label>

                <x-admin::media.images
                    name="account_owner_image"
                    :uploaded-images="$ownerProfileImages"
                />

                <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                    @lang('omicslogic::app.fields.owner-profile-help')
                </p>
            </x-admin::form.control-group>
        @endif

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

        <x-admin::form.control-group class="md:col-span-2">
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
