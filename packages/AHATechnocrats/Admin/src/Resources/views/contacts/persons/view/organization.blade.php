{!! view_render_event('admin.contacts.persons.view.organization.before', ['person' => $person]) !!}

@if ($person?->organization)
    @php
        $organization = $person->organization;
        $country = $organization->country_code
            ? app(\AHATechnocrats\OmicsLogic\Services\CountryLabelResolver::class)->resolve($organization->country_code)
            : null;
        $type = \AHATechnocrats\OmicsLogic\Enums\OrganizationType::tryFrom(strtolower($organization->type ?? ''));
    @endphp

    <div class="flex w-full flex-col gap-4 border-b border-gray-300 p-4 dark:border-gray-800">
        <h4 class="flex items-center justify-between font-semibold dark:text-white">
            @lang('admin::app.contacts.persons.view.about-organization')

            <a
                href="{{ route('admin.contacts.organizations.view', $organization->id) }}"
                class="icon-edit rounded-md p-1 text-2xl transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
            ></a>
        </h4>

        <div class="flex gap-2">
            <x-admin::avatar :name="$organization->name" />

            <div class="flex flex-col gap-1">
                <span class="font-semibold text-brandColor">{{ $organization->name }}</span>

                @if ($type)
                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ $type->label() }}</span>
                @endif

                @if ($country)
                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ $country }}</span>
                @endif

                @if ($organization->accountOwner)
                    <span class="text-sm text-gray-600 dark:text-gray-300">
                        @lang('omicslogic::app.fields.account-owner'): {{ $organization->accountOwner->name }}
                    </span>
                @endif

                @if ($organization->website)
                    <a href="{{ $organization->website }}" target="_blank" class="text-sm text-brandColor hover:underline">{{ $organization->website }}</a>
                @endif
            </div>
        </div>
    </div>
@endif

{!! view_render_event('admin.contacts.persons.view.organization.after', ['person' => $person]) !!}
