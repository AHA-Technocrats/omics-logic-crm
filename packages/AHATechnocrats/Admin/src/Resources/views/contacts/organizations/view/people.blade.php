{!! view_render_event('admin.contacts.organizations.view.people.before', ['organization' => $organization]) !!}

<div class="rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
        <h3 class="flex items-center gap-2 font-semibold dark:text-white">
            <span class="icon-user text-xl"></span>
            @lang('omicslogic::app.organizations.view.people')
        </h3>

        <span class="text-xs text-gray-500 dark:text-gray-400">
            @if ($organization->persons->count() > 5)
                5 of {{ $organization->persons->count() }}
            @else
                {{ $organization->persons->count() }}
            @endif
            @lang('omicslogic::app.organizations.view.shown')
        </span>
    </div>

    <div class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse ($organization->persons->take(5) as $person)
            @php

                $subtitle = collect([
                    $person->job_title ?? null,
                    $person->education_level ?? null,
                ])->filter()->implode(' · ');
            @endphp

            <a
                href="{{ route('admin.contacts.persons.view', $person->id) }}"
                class="flex items-center gap-3 px-4 py-3 transition hover:bg-gray-50 dark:hover:bg-gray-950"
            >
                <x-admin::avatar :name="$person->name" />

                <div class="min-w-0 flex-1">
                    <p class="truncate font-semibold dark:text-white">{{ $person->name }}</p>
                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                        {{ $subtitle ?: ($person->emails[0]['value'] ?? '') }}
                    </p>
                </div>


            </a>
        @empty
            <p class="px-4 py-8 text-center text-sm text-gray-600 dark:text-gray-300">
                @lang('omicslogic::app.organizations.view.no-people')
            </p>
        @endforelse
    </div>

    @if ($organization->persons->count() > 5)
        <a
            href="{{ route('admin.contacts.persons.index', ['organization[in]' => $organization->name]) }}"
            class="block border-t border-gray-200 bg-gray-50 px-4 py-2 text-center text-sm font-medium text-brandColor transition hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-900 dark:hover:bg-gray-800"
        >
            View all {{ $organization->persons->count() }} people
        </a>
    @endif
</div>

{!! view_render_event('admin.contacts.organizations.view.people.after', ['organization' => $organization]) !!}
