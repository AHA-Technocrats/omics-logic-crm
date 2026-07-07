@php
    $country = $person->organization?->country_code
        ? app(\AHATechnocrats\OmicsLogic\Services\CountryLabelResolver::class)->resolve($person->organization->country_code)
        : ($person->country_code
            ? app(\AHATechnocrats\OmicsLogic\Services\CountryLabelResolver::class)->resolve($person->country_code)
            : null);
    $stage = \AHATechnocrats\OmicsLogic\Enums\LifecycleStage::tryFrom($person->lifecycle_stage ?? '');
@endphp

{!! view_render_event('admin.contacts.persons.view.profile.before', ['person' => $person]) !!}

<div class="flex w-full flex-col gap-4 border-b border-gray-300 p-4 dark:border-gray-800">
    <div class="flex items-center justify-between">
        <h4 class="font-semibold dark:text-white">
            @lang('omicslogic::app.fields.contact-profile')
        </h4>

        @if (bouncer()->hasPermission('persons.edit'))
            <a
                href="{{ route('admin.contacts.persons.edit', $person->id) }}"
                class="text-sm font-medium text-brandColor hover:underline"
            >
                @lang('admin::app.contacts.persons.index.datagrid.edit')
            </a>
        @endif
    </div>

    <dl class="grid grid-cols-1 gap-3 text-sm">
        <div class="flex justify-between gap-4">
            <dt class="text-gray-600 dark:text-gray-300">@lang('admin::app.contacts.persons.index.datagrid.name')</dt>
            <dd class="text-right font-medium dark:text-white">{{ $person->name }}</dd>
        </div>

        <div class="flex justify-between gap-4">
            <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.fields.email')</dt>
            <dd class="text-right font-medium dark:text-white">{{ $person->emails[0]['value'] ?? '—' }}</dd>
        </div>

        <div class="flex justify-between gap-4">
            <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.fields.phone')</dt>
            <dd class="text-right font-medium dark:text-white">{{ $person->contact_numbers[0]['value'] ?? '—' }}</dd>
        </div>

        <div class="flex justify-between gap-4">
            <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.datagrid.organization')</dt>
            <dd class="text-right font-medium dark:text-white">
                @if ($person->organization)
                    <a href="{{ route('admin.contacts.organizations.view', $person->organization->id) }}" class="text-brandColor hover:underline">
                        {{ $person->organization->name }}
                    </a>
                @else
                    —
                @endif
            </dd>
        </div>

        <div class="flex justify-between gap-4">
            <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.fields.country')</dt>
            <dd class="text-right font-medium dark:text-white">{{ $country ?? '—' }}</dd>
        </div>

        <div class="flex justify-between gap-4">
            <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.fields.lifecycle-stage')</dt>
            <dd class="text-right font-medium dark:text-white">{{ $stage?->label() ?? ucfirst($person->lifecycle_stage ?? '—') }}</dd>
        </div>

        <div class="flex justify-between gap-4">
            <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.fields.education')</dt>
            <dd class="text-right font-medium dark:text-white">{{ $person->education_level ?? '—' }}</dd>
        </div>

        <div class="flex justify-between gap-4">
            <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.fields.inquiry-details')</dt>
            <dd class="text-right font-medium dark:text-white">{{ $person->inquiry_details ?? '—' }}</dd>
        </div>

        <div class="flex justify-between gap-4">
            <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.fields.campaign')</dt>
            <dd class="text-right font-medium dark:text-white">{{ $person->primaryProduct?->name ?? '—' }}</dd>
        </div>

        <div class="flex justify-between gap-4">
            <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.fields.source')</dt>
            <dd class="text-right font-medium dark:text-white">{{ $person->primarySource?->name ?? '—' }}</dd>
        </div>

        <div class="flex justify-between gap-4">
            <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.fields.owner')</dt>
            <dd class="text-right font-medium dark:text-white">{{ $person->user?->name ?? __('omicslogic::app.fields.unassigned') }}</dd>
        </div>
    </dl>

    @if (bouncer()->hasPermission('persons.edit'))
        <x-admin::form
            :action="route('admin.contacts.persons.update', $person->id)"
            method="PUT"
            class="mt-2 border-t border-gray-200 pt-4 dark:border-gray-800"
        >
            <input type="hidden" name="_redirect" value="view" />
            <input type="hidden" name="entity_type" value="persons" />
            <input type="hidden" name="name" value="{{ $person->name }}" />
            <input type="hidden" name="organization_id" value="{{ $person->organization_id }}" />

            @if ($person->emails[0]['value'] ?? null)
                <input type="hidden" name="emails[0][value]" value="{{ $person->emails[0]['value'] }}" />
                <input type="hidden" name="emails[0][label]" value="{{ $person->emails[0]['label'] ?? 'work' }}" />
            @endif

            @if ($person->contact_numbers[0]['value'] ?? null)
                <input type="hidden" name="contact_numbers[0][value]" value="{{ $person->contact_numbers[0]['value'] }}" />
                <input type="hidden" name="contact_numbers[0][label]" value="{{ $person->contact_numbers[0]['label'] ?? 'work' }}" />
            @endif

            @include('admin::omics.partials.person-fields', ['record' => $person, 'showHeading' => false, 'showCrmFields' => true])

            <div class="mt-4 flex justify-end">
                <button type="submit" class="primary-button">
                    @lang('admin::app.contacts.persons.edit.save-btn')
                </button>
            </div>
        </x-admin::form>
    @endif
</div>

{!! view_render_event('admin.contacts.persons.view.profile.after', ['person' => $person]) !!}
