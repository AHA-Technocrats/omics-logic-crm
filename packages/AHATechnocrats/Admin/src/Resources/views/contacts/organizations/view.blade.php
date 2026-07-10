<style>
    .icon-school {
        display: inline-block;
        width: 1em;
        height: 1em;
        background-color: currentColor;
        -webkit-mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='currentColor'><path d='M12 3L1 9l11 6 9-4.91V17h2V9L12 3z'/><path d='M4.14 11.18c-.09.26-.14.54-.14.82 0 2.21 3.58 4 8 4s8-1.79 8-4c0-.28-.05-.56-.14-.82L12 15.6l-7.86-4.42z'/></svg>") no-repeat center / contain;
        mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='currentColor'><path d='M12 3L1 9l11 6 9-4.91V17h2V9L12 3z'/><path d='M4.14 11.18c-.09.26-.14.54-.14.82 0 2.21 3.58 4 8 4s8-1.79 8-4c0-.28-.05-.56-.14-.82L12 15.6l-7.86-4.42z'/></svg>") no-repeat center / contain;
    }
</style>

@php
    $orgType = \AHATechnocrats\OmicsLogic\Enums\OrganizationType::tryFrom(strtolower($organization->type ?? ''));
    $country = $organization->country_code
        ? app(\AHATechnocrats\OmicsLogic\Services\CountryLabelResolver::class)->resolve($organization->country_code)
        : null;
    $typeIcon = match (strtolower($organization->type ?? '')) {
        'company' => 'icon-organization',
        'university', 'institute', 'college', 'school' => 'icon-school',
        default => 'icon-organization',
    };
@endphp

<x-admin::layouts>
    <x-slot:title>
        {{ $organization->name }}
    </x-slot>

    <div class="flex flex-col gap-4">
        {!! view_render_event('admin.contacts.organizations.view.before', ['organization' => $organization]) !!}

        <a
            href="{{ route('admin.contacts.organizations.index') }}"
            class="inline-flex w-fit items-center gap-1.5 text-sm font-medium text-gray-600 transition hover:text-brandColor dark:text-gray-300"
        >
            <span class="icon-left-arrow text-lg"></span>
            @lang('omicslogic::app.organizations.view.back')
        </a>

        <!-- Header -->
        <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex min-w-0 items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-red-700 text-white">
                        <span class="{{ $typeIcon }} text-2xl"></span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl font-bold dark:text-white">
                            {{ $organization->name }}
                        </h1>

                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-300">
                            <span class="inline-flex items-center gap-1">
                                <span class="icon-organization text-base"></span>
                                {{ $orgType?->label() ?? ucfirst($organization->type ?? 'organization') }}
                            </span>

                            @if ($country)
                                <span class="inline-flex items-center gap-1">
                                    <span class="icon-location text-base"></span>
                                    {{ $country }}
                                </span>
                            @endif

                            <span class="inline-flex items-center gap-1.5">
                                <span class="icon-user text-base"></span>
                                @lang('omicslogic::app.fields.account-owner'):
                                @if ($organization->accountOwner)
                                    @if ($organization->accountOwner->image)
                                        <img
                                            src="{{ $organization->accountOwner->image_url }}"
                                            alt="{{ $organization->accountOwner->name }}"
                                            class="h-6 w-6 shrink-0 rounded-full object-cover"
                                        />
                                    @else
                                        <x-admin::avatar :name="$organization->accountOwner->name" class="h-6 w-6" />
                                    @endif
                                    <span>{{ $organization->accountOwner->name }}</span>
                                @else
                                    <span class="text-gray-400">@lang('omicslogic::app.fields.unassigned')</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                @if (bouncer()->hasPermission('organizations.edit'))
                    <a
                        href="{{ route('admin.contacts.organizations.edit', $organization->id) }}"
                        class="secondary-button shrink-0"
                    >
                        <span class="icon-user text-lg"></span>
                        @lang('omicslogic::app.organizations.view.reassign')
                    </a>
                @endif
            </div>
        </div>

        <!-- Stats -->
        <div class="flex flex-wrap gap-4">
            <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900" style="flex: 1; min-width: 220px;">
                <p class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <span class="icon-user text-base"></span>
                    @lang('omicslogic::app.organizations.view.contacts')
                </p>
                <p class="mt-1 text-2xl font-bold dark:text-white">{{ number_format($stats['contacts']) }}</p>
            </div>

            <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900" style="flex: 1; min-width: 220px;">
                <p class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <span class="icon-activity text-base"></span>
                    @lang('omicslogic::app.organizations.view.engaged')
                </p>
                <p class="mt-1 text-2xl font-bold dark:text-white">{{ number_format($stats['engaged']) }}</p>
            </div>

            <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900" style="flex: 1; min-width: 220px;">
                <p class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <span class="icon-leads text-base"></span>
                    @lang('omicslogic::app.organizations.view.customers')
                </p>
                <p class="mt-1 text-2xl font-bold dark:text-white">{{ number_format($stats['customers']) }}</p>
            </div>

            <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900" style="flex: 1; min-width: 220px;">
                <p class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <span class="icon-quote text-base"></span>
                    @lang('omicslogic::app.organizations.view.est-account-value')
                </p>
                <p class="mt-1 text-2xl font-bold dark:text-white">{!! core()->formatBasePrice($stats['estimated_value'], true) !!}</p>
            </div>
        </div>

        <!-- Two-column content -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="flex flex-col gap-4">
                @include('admin::contacts.organizations.view.profile')
                @include('admin::contacts.organizations.view.notes')
            </div>

            <div class="flex flex-col gap-4">
                @include('admin::contacts.organizations.view.people')
                @include('admin::contacts.organizations.view.activity')
            </div>
        </div>

        {!! view_render_event('admin.contacts.organizations.view.after', ['organization' => $organization]) !!}
    </div>
</x-admin::layouts>
