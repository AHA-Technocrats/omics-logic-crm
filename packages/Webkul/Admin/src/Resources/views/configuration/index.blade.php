<x-admin::layouts>
    <x-slot:title>
        Connectors
    </x-slot>

    @php
        $connectors = [
            [
                'title' => 'Google Forms',
                'icon' => 'fa-regular fa-file-lines',
                'icon_class' => 'connectors-icon--blue',
                'status' => [
                    'label' => 'Connected',
                    'class' => 'connectors-status--connected',
                    'dot' => true,
                ],
                'meta' => [
                    'Sheets API · 14 registration forms',
                    'Syncs hourly · last run 2h ago',
                ],
                'action' => [
                    'label' => 'Manage forms & field maps',
                    'icon' => 'fa-solid fa-gear',
                    'class' => 'connectors-action--secondary',
                    'href' => '#',
                ],
            ],
            [
                'title' => 'OmicsLogic Portal',
                'icon' => 'fa-solid fa-graduation-cap',
                'icon_class' => 'connectors-icon--purple',
                'status' => [
                    'label' => 'Reauthorize',
                    'class' => 'connectors-status--warning',
                    'dot' => true,
                ],
                'meta' => [
                    'learn + edu · lessons, ratings, userId',
                    'Nightly + events · token expired 5h ago',
                ],
                'action' => [
                    'label' => 'Reconnect',
                    'icon' => 'fa-solid fa-rotate',
                    'class' => 'connectors-action--primary',
                    'href' => '#',
                ],
            ],
            [
                'title' => 'CSV / Excel import',
                'icon' => 'fa-solid fa-file-excel',
                'icon_class' => 'connectors-icon--green',
                'status' => [
                    'label' => 'Manual',
                    'class' => 'connectors-status--neutral',
                    'dot' => false,
                ],
                'meta' => [
                    'For backfill & one-off lists',
                    'Last used Jan 2025 · Zoho export',
                ],
                'action' => [
                    'label' => 'New import',
                    'icon' => 'fa-solid fa-arrow-up-from-bracket',
                    'class' => 'connectors-action--secondary',
                    'href' => route('admin.settings.data_transfer.imports.index'),
                ],
            ],
            [
                'title' => 'Zoho CRM (legacy)',
                'icon' => 'fa-solid fa-database',
                'icon_class' => 'connectors-icon--slate',
                'status' => [
                    'label' => 'Archived',
                    'class' => 'connectors-status--neutral',
                    'dot' => false,
                ],
                'meta' => [
                    'One-time migration complete',
                    '35,112 records imported',
                ],
                'action' => [
                    'label' => 'View mapping',
                    'icon' => 'fa-regular fa-eye',
                    'class' => 'connectors-action--secondary',
                    'href' => '#',
                ],
            ],
        ];
    @endphp

    {!! view_render_event('admin.configuration.index.header.before') !!}

    <section class="connectors-page">
        <div class="connectors-hero">
            <h1>Connectors</h1>
            <p>Where the data comes from. Add a new workshop form by pasting its responses-sheet URL and picking a field map.</p>
        </div>

        {!! view_render_event('admin.configuration.index.content.before') !!}

        <div class="connectors-grid">
            @foreach ($connectors as $connector)
                <article class="connectors-card">
                    <div class="connectors-card__head">
                        <div class="connectors-card__icon {{ $connector['icon_class'] }}">
                            <i class="{{ $connector['icon'] }}"></i>
                        </div>

                        <span class="connectors-status {{ $connector['status']['class'] }}">
                            @if ($connector['status']['dot'])
                                <span class="connectors-status__dot"></span>
                            @endif

                            {{ $connector['status']['label'] }}
                        </span>
                    </div>

                    <h2 class="connectors-card__title">{{ $connector['title'] }}</h2>

                    @foreach ($connector['meta'] as $line)
                        <p class="connectors-card__meta">{{ $line }}</p>
                    @endforeach

                    <a
                        href="{{ $connector['action']['href'] }}"
                        class="connectors-action {{ $connector['action']['class'] }}"
                    >
                        <i class="{{ $connector['action']['icon'] }}"></i>
                        {{ $connector['action']['label'] }}
                    </a>
                </article>
            @endforeach

            <button
                type="button"
                class="connectors-card connectors-card--add"
            >
                <strong>Add connector</strong>
                <span>New form, webhook, or data source</span>
            </button>
        </div>

        {!! view_render_event('admin.configuration.index.content.after') !!}
    </section>

    {!! view_render_event('admin.configuration.index.header.after') !!}
</x-admin::layouts>
