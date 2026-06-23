<x-admin::layouts>
    <x-slot:title>
        Imports &amp; sync
    </x-slot>

    @php
        $metrics = [
            ['icon' => 'ti ti-plug-connected', 'label' => 'SOURCES CONNECTED', 'value' => '4', 'note' => '14 forms + portal'],
            ['icon' => 'ti ti-clock', 'label' => 'LAST SYNC', 'value' => '1h ago', 'note' => 'portal events'],
            ['icon' => 'ti ti-database-import', 'label' => 'ROWS INGESTED (30D)', 'value' => '2,140', 'note' => 'auto-deduped'],
            ['icon' => 'ti ti-alert-triangle', 'label' => 'NEEDS ATTENTION', 'value' => '1', 'note' => 'portal auth expired', 'attention' => true],
        ];

        $runs = [
            ['source' => 'scRNA-Seq workshop', 'type' => 'Google Form', 'icon' => 'ti ti-forms', 'rows' => '88', 'new' => '+81', 'merged' => '5', 'review' => '2', 'status' => ['label' => 'success', 'class' => 'imports-status--success'], 'when' => '2h ago'],
            ['source' => 'Portal activity sync', 'type' => 'Portal', 'icon' => 'ti ti-world', 'rows' => '1,240', 'new' => '—', 'merged' => '—', 'review' => '—', 'status' => ['label' => 'success', 'class' => 'imports-status--success'], 'when' => '1h ago'],
            ['source' => 'From Sample to Sequencer', 'type' => 'Google Form', 'icon' => 'ti ti-forms', 'rows' => '161', 'new' => '+150', 'merged' => '9', 'review' => '2', 'status' => ['label' => 'success', 'class' => 'imports-status--success'], 'when' => 'overnight'],
            ['source' => 'AI in Drug Discovery', 'type' => 'Google Form', 'icon' => 'ti ti-forms', 'rows' => '142', 'new' => '+131', 'merged' => '8', 'review' => '3', 'status' => ['label' => 'success', 'class' => 'imports-status--success'], 'when' => '1d ago'],
            ['source' => 'Zoho CRM backfill 2024', 'type' => 'CSV upload', 'icon' => 'ti ti-file-spreadsheet', 'rows' => '35,112', 'new' => '+29,400', 'merged' => '4,900', 'review' => '812', 'status' => ['label' => 'completed', 'class' => 'imports-status--success'], 'when' => 'Jan 2025'],
            ['source' => 'Portal activity sync', 'type' => 'Portal', 'icon' => 'ti ti-world', 'rows' => '0', 'new' => '—', 'merged' => '—', 'review' => '—', 'status' => ['label' => 'failed · auth expired', 'class' => 'imports-status--failed'], 'when' => '5h ago'],
        ];
    @endphp

    {!! view_render_event('admin.settings.data_transfers.index.content.before') !!}

    <div class="imports-page">
        <section class="imports-hero">
            <h1>Imports &amp; sync</h1>
            <p>Every run from every source — what came in, how many were new, merged, or parked for review.</p>
        </section>

        <section class="imports-metrics">
            @foreach ($metrics as $metric)
                <article class="imports-metric {{ ! empty($metric['attention']) ? 'imports-metric--attention' : '' }}">
                    <div class="imports-metric__label">
                        <i class="{{ $metric['icon'] }}"></i>
                        {{ $metric['label'] }}
                    </div>

                    <div class="imports-metric__value">{{ $metric['value'] }}</div>
                    <div class="imports-metric__note">{{ $metric['note'] }}</div>
                </article>
            @endforeach
        </section>

        <section class="imports-dropzone">
            <h2>Drop a CSV or Excel file to backfill history</h2>
            <p>Map columns → preview dedup → import. Used for the legacy Zoho export and one-off lists.</p>

            <a
                href="{{ route('admin.settings.data_transfer.imports.create') }}"
                class="imports-choose-button"
            >
                <i class="ti ti-upload"></i>
                Choose file
            </a>
        </section>

        <section class="imports-table-shell">
            <div class="imports-table-tools">
                <h2>Recent runs</h2>

                <button type="button">
                    <i class="ti ti-refresh"></i>
                    Sync now
                </button>
            </div>

            <div class="imports-table">
                <table>
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th>Type</th>
                            <th>Rows</th>
                            <th>New</th>
                            <th>Merged</th>
                            <th>Review</th>
                            <th>Status</th>
                            <th>When</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($runs as $run)
                            <tr>
                                <td class="imports-source">{{ $run['source'] }}</td>
                                <td>
                                    <span class="imports-type">
                                        <i class="{{ $run['icon'] }}"></i>
                                        {{ $run['type'] }}
                                    </span>
                                </td>
                                <td class="imports-number">{{ $run['rows'] }}</td>
                                <td class="imports-new">{{ $run['new'] }}</td>
                                <td class="imports-merged">{{ $run['merged'] }}</td>
                                <td class="imports-review">{{ $run['review'] }}</td>
                                <td>
                                    <span class="imports-status {{ $run['status']['class'] }}">
                                        <i class="ti ti-check"></i>
                                        {{ $run['status']['label'] }}
                                    </span>
                                </td>
                                <td class="imports-when">{{ $run['when'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {!! view_render_event('admin.settings.data_transfers.index.content.after') !!}
</x-admin::layouts>
