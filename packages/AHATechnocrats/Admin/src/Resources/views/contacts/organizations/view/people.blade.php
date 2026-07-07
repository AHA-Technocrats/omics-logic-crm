{!! view_render_event('admin.contacts.organizations.view.people.before', ['organization' => $organization]) !!}

<div class="rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
        <h3 class="flex items-center gap-2 font-semibold dark:text-white">
            <span class="icon-user text-xl"></span>
            @lang('omicslogic::app.organizations.view.people')
        </h3>

        <span class="text-xs text-gray-500 dark:text-gray-400">
            {{ $organization->persons->count() }} @lang('omicslogic::app.organizations.view.shown')
        </span>
    </div>

    <div class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse ($organization->persons as $person)
            @php
                $stage = \AHATechnocrats\OmicsLogic\Enums\LifecycleStage::tryFrom($person->lifecycle_stage ?? '');
                $stageLabel = $stage?->label() ?? ucfirst($person->lifecycle_stage ?? 'subscriber');
                $stageClass = match ($person->lifecycle_stage) {
                    'customer' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                    'engaged' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                    'lead' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                    'dormant' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                    default => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
                };
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

                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $stageClass }}">
                    {{ $stageLabel }}
                </span>
            </a>
        @empty
            <p class="px-4 py-8 text-center text-sm text-gray-600 dark:text-gray-300">
                @lang('omicslogic::app.organizations.view.no-people')
            </p>
        @endforelse
    </div>
</div>

{!! view_render_event('admin.contacts.organizations.view.people.after', ['organization' => $organization]) !!}
