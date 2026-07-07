@php
    $ownerName = $organization->accountOwner?->name ?? __('omicslogic::app.fields.unassigned');
    $timeline = [
        [
            'icon' => 'icon-quote',
            'iconClass' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
            'title' => __('omicslogic::app.organizations.view.timeline.discussions'),
            'detail' => __('omicslogic::app.organizations.view.timeline.owner', ['name' => $ownerName]),
            'when' => __('omicslogic::app.organizations.view.timeline.recent'),
        ],
        [
            'icon' => 'icon-note',
            'iconClass' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
            'title' => __('omicslogic::app.organizations.view.timeline.registrations'),
            'detail' => __('omicslogic::app.organizations.view.timeline.registrations-detail'),
            'when' => __('omicslogic::app.organizations.view.timeline.this-quarter'),
        ],
        [
            'icon' => 'icon-activity',
            'iconClass' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
            'title' => __('omicslogic::app.organizations.view.timeline.lessons'),
            'detail' => __('omicslogic::app.organizations.view.timeline.lessons-detail'),
            'when' => __('omicslogic::app.organizations.view.timeline.ongoing'),
        ],
    ];
@endphp

{!! view_render_event('admin.contacts.organizations.view.activity.before', ['organization' => $organization]) !!}

<div class="rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
        <h3 class="flex items-center gap-2 font-semibold dark:text-white">
            <span class="icon-activity text-xl"></span>
            @lang('omicslogic::app.organizations.view.account-activity')
        </h3>
    </div>

    <div class="flex flex-col gap-0 p-4">
        @foreach ($timeline as $index => $item)
            <div class="flex gap-3">
                <div class="flex flex-col items-center">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full {{ $item['iconClass'] }}">
                        <span class="{{ $item['icon'] }} text-lg"></span>
                    </div>

                    @if (! $loop->last)
                        <div class="my-1 w-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                    @endif
                </div>

                <div class="flex min-w-0 flex-1 items-start justify-between gap-3 pb-5">
                    <div>
                        <p class="text-sm font-semibold dark:text-white">{{ $item['title'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['detail'] }}</p>
                    </div>

                    <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">{{ $item['when'] }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>

{!! view_render_event('admin.contacts.organizations.view.activity.after', ['organization' => $organization]) !!}
