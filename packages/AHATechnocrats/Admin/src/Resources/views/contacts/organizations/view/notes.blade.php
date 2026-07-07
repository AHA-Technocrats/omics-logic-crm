@php
    $noteLines = array_values(array_filter(
        array_map('trim', preg_split('/\r\n|\r|\n/', $organization->notes ?? '') ?: [])
    ));
@endphp

{!! view_render_event('admin.contacts.organizations.view.notes.before', ['organization' => $organization]) !!}

<div class="rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
        <h3 class="flex items-center gap-2 font-semibold dark:text-white">
            <span class="icon-note text-xl"></span>
            @lang('omicslogic::app.organizations.view.notes-next-steps')
        </h3>
    </div>

    <div class="flex flex-col gap-3 p-4">
        @forelse ($noteLines as $line)
            <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300">
                <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded border border-gray-300 dark:border-gray-600"></span>
                <span>{{ $line }}</span>
            </label>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">
                @lang('omicslogic::app.organizations.view.no-notes')
            </p>
        @endforelse

        @if (bouncer()->hasPermission('organizations.edit'))
            <a
                href="{{ route('admin.contacts.organizations.edit', $organization->id) }}"
                class="secondary-button mt-1 w-full justify-center"
            >
                @lang('omicslogic::app.organizations.view.add-note')
            </a>
        @endif
    </div>
</div>

{!! view_render_event('admin.contacts.organizations.view.notes.after', ['organization' => $organization]) !!}
