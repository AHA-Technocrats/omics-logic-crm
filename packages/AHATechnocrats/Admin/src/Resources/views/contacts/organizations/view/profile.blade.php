@php
    $country = $organization->country_code
        ? app(\AHATechnocrats\OmicsLogic\Services\CountryLabelResolver::class)->resolve($organization->country_code)
        : null;
    $type = \AHATechnocrats\OmicsLogic\Enums\OrganizationType::tryFrom(strtolower($organization->type ?? ''));
@endphp

{!! view_render_event('admin.contacts.organizations.view.profile.before', ['organization' => $organization]) !!}

<div class="rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
        <h3 class="flex items-center gap-2 font-semibold dark:text-white">
            <span class="icon-organization text-xl"></span>
            @lang('omicslogic::app.organizations.view.account')
        </h3>

        @if (bouncer()->hasPermission('organizations.edit'))
            <a
                href="{{ route('admin.contacts.organizations.edit', $organization->id) }}"
                class="inline-flex items-center gap-1 text-sm font-medium text-brandColor hover:underline"
            >
                <span class="icon-edit text-base"></span>
                @lang('admin::app.contacts.organizations.index.datagrid.edit')
            </a>
        @endif
    </div>

    <dl class="divide-y divide-gray-100 dark:divide-gray-800">
        <div class="flex items-center justify-between gap-4 px-4 py-3 text-sm">
            <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.fields.type')</dt>
            <dd class="font-semibold dark:text-white">{{ $type?->label() ?? ucfirst($organization->type ?? '—') }}</dd>
        </div>

        <div class="flex items-center justify-between gap-4 px-4 py-3 text-sm">
            <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.fields.country')</dt>
            <dd class="font-semibold dark:text-white">{{ $country ?? '—' }}</dd>
        </div>

        <div class="flex items-center justify-between gap-4 px-4 py-3 text-sm">
            <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.organizations.view.top-program')</dt>
            <dd class="text-right font-semibold dark:text-white">{{ $topProgram ?? '—' }}</dd>
        </div>

        <div class="flex items-center justify-between gap-4 px-4 py-3 text-sm">
            <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.fields.account-owner')</dt>
            <dd class="font-semibold dark:text-white">{{ $organization->accountOwner?->name ?? __('omicslogic::app.fields.unassigned') }}</dd>
        </div>

        @if ($organization->website)
            <div class="flex items-center justify-between gap-4 px-4 py-3 text-sm">
                <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.fields.website')</dt>
                <dd class="font-semibold">
                    <a href="{{ $organization->website }}" target="_blank" class="text-brandColor hover:underline">{{ $organization->website }}</a>
                </dd>
            </div>
        @endif
    </dl>
</div>

{!! view_render_event('admin.contacts.organizations.view.profile.after', ['organization' => $organization]) !!}
