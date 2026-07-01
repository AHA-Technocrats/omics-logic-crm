<x-admin::layouts>
    <x-slot:title>
        Audit log
    </x-slot>

    @php
        $filters = [
            ['label' => 'Actor', 'type' => 'select', 'value' => 'Everyone'],
            ['label' => 'Action', 'type' => 'select', 'value' => 'All actions'],
            ['label' => 'Date', 'type' => 'date', 'value' => '2026-06-08'],
        ];

        $logs = [
            [
                'when' => 'Today 10:24',
                'actor' => ['initials' => 'MM', 'name' => 'Mohit M.', 'class' => 'audit-avatar--green'],
                'action' => 'Merged contacts',
                'entity' => 'Priya Nair (m1)',
                'details' => '2 records -> 1 - phone+name match',
                'undo' => true,
            ],
            [
                'when' => 'Today 09:50',    
                'actor' => ['icon' => 'fa-solid fa-filter', 'name' => 'Forms connector', 'class' => 'audit-avatar--slate'],
                'action' => 'Imported 88 rows',
                'entity' => 'scRNA-Seq workshop',
                'details' => '81 new - 5 merged - 2 to review',
                'undo' => false,
            ],
            [
                'when' => 'Today 09:12',
                'actor' => ['initials' => 'OD', 'name' => 'Ojasvi D.', 'class' => 'audit-avatar--teal'],
                'action' => 'Edited contact',
                'entity' => 'Zahra Nouri',
                'details' => 'country: blank -> Iran',
                'undo' => true,
            ],
            [
                'when' => 'Today 08:30',
                'actor' => ['icon' => 'fa-solid fa-filter', 'name' => 'Dedup service', 'class' => 'audit-avatar--slate'],
                'action' => 'Auto-merged 47 contacts',
                'entity' => 'batch',
                'details' => 'confidence >= 0.95',
                'undo' => true,
            ],
            [
                'when' => 'Yesterday',
                'actor' => ['initials' => 'MM', 'name' => 'Mohit M.', 'class' => 'audit-avatar--green'],
                'action' => 'Exported segment',
                'entity' => 'India - faculty',
                'details' => '312 rows - CSV',
                'undo' => false,
            ],
            [
                'when' => 'Yesterday',
                'actor' => ['initials' => 'MM', 'name' => 'Mohit M.', 'class' => 'audit-avatar--green'],
                'action' => 'Approved campaign aliases',
                'entity' => 'RNA-Seq Data Analysis',
                'details' => '5 variants mapped',
                'undo' => true,
            ],
            [
                'when' => '2 days ago',
                'actor' => ['initials' => 'H', 'name' => 'Harshita', 'class' => 'audit-avatar--red'],
                'action' => 'Logged in',
                'entity' => '&mdash;',
                'details' => 'role: Editor',
                'undo' => false,
            ],
        ];
    @endphp

    <section class="audit-log-page">
        <div class="audit-log-hero">
            <h1>Audit log</h1>
            <p>Every change &mdash; by a person or a connector &mdash; is recorded and, where it matters, reversible.</p>
        </div>

        <section class="audit-log-filters">
            @foreach ($filters as $filter)
                <div class="audit-log-filter">
                    <label>{{ $filter['label'] }}</label>

                    @if ($filter['type'] === 'date')
                        <div class="audit-log-date">
                            <input
                                id="audit-log-date"
                                type="date"
                                value="2026-06-08"
                            >

                            <button
                                type="button"
                                class="audit-log-date__button"
                                aria-label="Open calendar"
                                onclick="document.getElementById('audit-log-date')?.showPicker ? document.getElementById('audit-log-date').showPicker() : document.getElementById('audit-log-date')?.focus()"
                            >
                                <i class="fa-regular fa-calendar"></i>
                            </button>
                        </div>
                    @else
                        <div class="audit-log-select">
                            <select>
                                <option>{{ $filter['value'] }}</option>
                            </select>
                        </div>
                    @endif
                </div>
            @endforeach

            <button
                type="button"
                class="audit-log-clear"
            >
                <span>×</span>
                Clear
            </button>
        </section>

        <section class="audit-log-table">
            <table>
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Actor</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Details</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td>{{ $log['when'] }}</td>

                            <td>
                                <div class="audit-log-actor">
                                    <span class="audit-avatar {{ $log['actor']['class'] }}">
                                        @if (! empty($log['actor']['icon']))
                                            <i class="{{ $log['actor']['icon'] }}"></i>
                                        @else
                                            {{ $log['actor']['initials'] }}
                                        @endif
                                    </span>

                                    <span>{{ $log['actor']['name'] }}</span>
                                </div>
                            </td>

                            <td><strong>{{ $log['action'] }}</strong></td>
                            <td>{!! $log['entity'] !!}</td>
                            <td>{{ $log['details'] }}</td>

                            <td>
                                @if ($log['undo'])
                                    <button
                                        type="button"
                                        class="audit-log-undo"
                                    >
                                        <i class="fa-solid fa-rotate-left"></i>
                                        Undo
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </section>
</x-admin::layouts>
