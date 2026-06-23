<x-admin::layouts>
    <x-slot:title>
        Segments
    </x-slot>

    @php
        $metrics = [
            ['icon' => 'ti ti-bookmark', 'label' => 'SAVED SEGMENTS', 'value' => '14', 'note' => '6 shared with team'],
            ['icon' => 'ti ti-refresh', 'label' => 'AUTO-REFRESHING', 'value' => '9', 'note' => 'daily or weekly'],
            ['icon' => 'ti ti-users', 'label' => 'LARGEST SEGMENT', 'value' => '5,941', 'note' => 'engaged, not customer'],
            ['icon' => 'ti ti-world', 'label' => 'USED IN OUTREACH', 'value' => '5', 'note' => 'exported this month'],
        ];

        $segments = [
            ['name' => 'Engaged, not customer', 'rule' => 'stage = Engaged · lessons ≥ 1 · no purchase', 'contacts' => '5,941', 'owner' => 'Mohit M.', 'refresh' => 'auto · daily'],
            ['name' => 'Dormant 90d+', 'rule' => 'last_activity > 90 days · was Engaged', 'contacts' => '4,870', 'owner' => 'System', 'refresh' => 'auto · daily'],
            ['name' => 'Transcriptomics interest', 'rule' => 'research_interest contains RNA-Seq / scRNA', 'contacts' => '2,104', 'owner' => 'Ojasvi D.', 'refresh' => 'auto · weekly'],
            ['name' => 'India · faculty', 'rule' => 'country = India · education = Faculty', 'contacts' => '312', 'owner' => 'Mohit M.', 'refresh' => 'manual'],
            ['name' => 'NGS form — cold leads', 'rule' => 'campaign = Sample to Sequencer · lessons = 0', 'contacts' => '143', 'owner' => 'Harshita', 'refresh' => 'auto · daily'],
            ['name' => 'PGDP alumni', 'rule' => 'tag = PGDP · stage = Customer', 'contacts' => '88', 'owner' => 'Mohit M.', 'refresh' => 'manual'],
        ];
    @endphp

    {!! view_render_event('admin.segment.index.content.before') !!}

    <div class="segments-page">
        <section class="segments-hero">
            <h1>Segments</h1>
            <p>Saved, named filters that stay live — build the audience once, it refreshes itself, and any tool or report can read it.</p>
        </section>

        <section class="segments-metrics">
            @foreach ($metrics as $metric)
                <article class="segments-metric">
                    <div class="segments-metric__label">
                        <i class="{{ $metric['icon'] }}"></i>
                        {{ $metric['label'] }}
                    </div>

                    <div class="segments-metric__value">{{ $metric['value'] }}</div>
                    <div class="segments-metric__note">{{ $metric['note'] }}</div>
                </article>
            @endforeach
        </section>

        <section class="segments-table-shell">
            <div class="segments-table-tools">
                <div class="segments-table-tools__count">
                    Showing <strong>6</strong> of <strong>14</strong> segments
                </div>

                <button
                    type="button"
                    class="segments-new-button"
                >
                    <i class="ti ti-plus"></i>
                    New segment
                </button>
            </div>

            <div class="segments-table">
                <table>
                    <thead>
                        <tr>
                            <th>Segment</th>
                            <th>Contacts</th>
                            <th>Owner</th>
                            <th>Refresh</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($segments as $segment)
                            <tr>
                                <td>
                                    <div class="segments-name">
                                        <div>
                                            <i class="ti ti-bookmark"></i>
                                            <strong>{{ $segment['name'] }}</strong>
                                        </div>

                                        <p>{{ $segment['rule'] }}</p>
                                    </div>
                                </td>

                                <td class="segments-count">{{ $segment['contacts'] }}</td>
                                <td>{{ $segment['owner'] }}</td>
                                <td>
                                    <span class="segments-refresh">
                                        <i class="ti ti-refresh"></i>
                                        {{ $segment['refresh'] }}
                                    </span>
                                </td>

                                <td>
                                    <div class="segments-actions">
                                        <span><i class="ti ti-eye"></i> Open</span>
                                        <span><i class="ti ti-download"></i> Export</span>
                                        <span>···</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {!! view_render_event('admin.segment.index.content.after') !!}
</x-admin::layouts>
