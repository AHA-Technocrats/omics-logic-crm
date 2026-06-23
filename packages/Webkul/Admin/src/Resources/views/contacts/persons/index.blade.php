<x-admin::layouts>
    <x-slot:title>
        Contacts
    </x-slot>
@php
        $metrics = [
            ['icon' => 'ti ti-users', 'label' => 'TOTAL CONTACTS', 'value' => '34,812', 'note' => '+612 this month'],
            ['icon' => 'ti ti-user-plus', 'label' => 'NEW LEADS (30D)', 'value' => '612', 'note' => '+18% vs last'],
            ['icon' => 'ti ti-flame', 'label' => 'ENGAGED LEARNERS', 'value' => '5,941', 'note' => '&ge;1 lesson done'],
            ['icon' => 'ti ti-crown', 'label' => 'CUSTOMERS', 'value' => '1,287', 'note' => '3.7% lifetime conversion'],
            ['icon' => 'ti ti-sparkles', 'label' => 'NEEDS REVIEW', 'value' => '23', 'note' => 'possible duplicates', 'review' => true],
        ];

        $segments = [
            ['icon' => 'ti ti-star', 'label' => 'All contacts', 'active' => true],
            ['icon' => 'ti ti-flame', 'label' => 'Engaged, not customer'],
            ['icon' => 'ti ti-diamond', 'label' => 'India · faculty'],
            ['icon' => 'ti ti-bolt', 'label' => 'Transcriptomics interest'],
            ['icon' => 'ti ti-moon', 'label' => 'Dormant 90d+'],
            ['icon' => 'ti ti-plus', 'label' => 'New segment'],
        ];

        $filters = [
            ['label' => 'Country', 'value' => 'All countries'],
            ['label' => 'Lifecycle stage', 'value' => 'Any stage'],
            ['label' => 'Program / interest', 'value' => 'Any program'],
            ['label' => 'Source', 'value' => 'Any source'],
            ['label' => 'Education', 'value' => 'Any level'],
            ['label' => 'Engagement', 'value' => 'Completed &ge;1 lesson'],
            ['label' => 'Owner', 'value' => 'Any owner'],
        ];

        $contacts = [
            [
                'initials' => 'PN',
                'avatar' => 'contacts-avatar--red',
                'name' => 'Priya Nair',
                'merged' => true,
                'email' => 'priya.nair@iitj.ac.in',
                'organization' => 'IIT Jodhpur',
                'country' => 'India',
                'stage' => ['label' => 'Customer', 'class' => 'contacts-stage--customer'],
                'program' => 'Single-Cell RNA-Seq',
                'source' => 'Portal',
                'lessons' => '14',
                'score' => ['value' => '92', 'class' => 'contacts-score--hot'],
                'owner' => ['initials' => 'OD', 'name' => 'Ojasvi Dutta', 'class' => 'contacts-owner__avatar--gold'],
                'last' => '2d ago',
            ],
            [
                'initials' => 'CS',
                'avatar' => 'contacts-avatar--gold',
                'name' => 'Charith Sumeet',
                'email' => 'charism@udel.edu',
                'organization' => 'University of Delaware',
                'country' => 'United States',
                'stage' => ['label' => 'Engaged', 'class' => 'contacts-stage--engaged'],
                'program' => 'From Sample to Sequencer (NGS)',
                'source' => 'Google Form',
                'lessons' => '6',
                'score' => ['value' => '68', 'class' => 'contacts-score--warm'],
                'owner' => ['initials' => 'HS', 'name' => 'Harshita Sharma', 'class' => 'contacts-owner__avatar--gold'],
                'last' => '6d ago',
            ],
            [
                'initials' => 'TA',
                'avatar' => 'contacts-avatar--purple',
                'name' => 'Tunde Adeyemi',
                'merged' => true,
                'email' => 't.adeyemi@unilag.edu.ng',
                'organization' => 'University of Lagos',
                'country' => 'Nigeria',
                'stage' => ['label' => 'Engaged', 'class' => 'contacts-stage--engaged'],
                'program' => 'Metagenomics',
                'source' => 'Referral',
                'lessons' => '4',
                'score' => ['value' => '71', 'class' => 'contacts-score--warm'],
                'owner' => ['initials' => 'MM', 'name' => 'Mohit M.', 'class' => 'contacts-owner__avatar--green'],
                'last' => '1d ago',
            ],
            [
                'initials' => 'LF',
                'avatar' => 'contacts-avatar--green',
                'name' => 'Lena Fischer',
                'merged' => true,
                'email' => 'lena.fischer@gmail.com',
                'organization' => 'McGill University',
                'country' => 'Canada',
                'stage' => ['label' => 'Customer', 'class' => 'contacts-stage--customer'],
                'program' => 'Machine Learning for Omics',
                'source' => 'Portal',
                'lessons' => '11',
                'score' => ['value' => '85', 'class' => 'contacts-score--hot'],
                'owner' => ['initials' => 'OD', 'name' => 'Ojasvi Dutta', 'class' => 'contacts-owner__avatar--gold'],
                'last' => '1d ago',
            ],
            [
                'initials' => 'RD',
                'avatar' => 'contacts-avatar--red',
                'name' => 'Rohan Das',
                'merged' => true,
                'email' => 'rohan.das@nibmg.ac.in',
                'organization' => 'NIBMG Kalyani',
                'country' => 'India',
                'stage' => ['label' => 'Dormant', 'class' => 'contacts-stage--dormant'],
                'program' => 'Clinical Genomics',
                'source' => 'Zoho import',
                'lessons' => '2',
                'score' => ['value' => '41', 'class' => 'contacts-score--cold'],
                'owner' => ['initials' => 'HS', 'name' => 'Harshita Sharma', 'class' => 'contacts-owner__avatar--gold'],
                'last' => '5mo ago',
            ],
            [
                'initials' => 'OH',
                'avatar' => 'contacts-avatar--gold',
                'name' => 'Omar Hassan',
                'email' => 'omar.hassan@cu.edu.eg',
                'organization' => 'Cairo University',
                'country' => 'Egypt',
                'stage' => ['label' => 'Engaged', 'class' => 'contacts-stage--engaged'],
                'program' => 'RNA-Seq Data Analysis',
                'source' => 'Portal',
                'lessons' => '7',
                'score' => ['value' => '64', 'class' => 'contacts-score--warm'],
                'owner' => ['initials' => 'OD', 'name' => 'Ojasvi Dutta', 'class' => 'contacts-owner__avatar--gold'],
                'last' => '4d ago',
            ],
            [
                'initials' => 'SR',
                'avatar' => 'contacts-avatar--teal',
                'name' => 'Sneha Reddy',
                'email' => 'sneha.reddy@gmail.com',
                'organization' => 'Independent',
                'country' => 'India',
                'stage' => ['label' => 'Engaged', 'class' => 'contacts-stage--engaged'],
                'program' => 'AI in Drug Discovery',
                'source' => 'Google Form',
                'lessons' => '5',
                'score' => ['value' => '59', 'class' => 'contacts-score--warm'],
                'owner' => ['initials' => 'MM', 'name' => 'Mohit M.', 'class' => 'contacts-owner__avatar--green'],
                'last' => '8d ago',
            ],
        ];
    @endphp

    {!! view_render_event('admin.persons.index.content.before') !!}

    <section class="contacts-hero">
        <h1>Contacts</h1>
        <p>One unified record per person — merged from Google Forms, the OmicsLogic portal, and legacy CRM imports.</p>
    </section>

    <div class="contacts-page">
        <section class="contacts-metrics">
            @foreach ($metrics as $metric)
                <article class="contacts-metric {{ ! empty($metric['review']) ? 'contacts-metric--review' : '' }}">
                    <div class="contacts-metric__label">
                        <i class="{{ $metric['icon'] }}"></i>
                        {{ $metric['label'] }}
                    </div>

                    <div class="contacts-metric__value">{{ $metric['value'] }}</div>
                    <div class="contacts-metric__note">{!! $metric['note'] !!}</div>
                </article>
            @endforeach
        </section>

        <section class="contacts-segments">
            <span class="contacts-segments__label">Saved segments:</span>

            @foreach ($segments as $segment)
                <button
                    type="button"
                    class="contacts-chip {{ ! empty($segment['active']) ? 'contacts-chip--active' : '' }}"
                >
                    <i class="{{ $segment['icon'] }}"></i>
                    {{ $segment['label'] }}
                </button>
            @endforeach
        </section>

        <section class="contacts-filters">
            @foreach ($filters as $filter)
                <div class="contacts-filter">
                    <label>{{ $filter['label'] }}</label>
                    <div class="contacts-filter__select">
                        <select>
                            <option>{!! $filter['value'] !!}</option>
                        </select>
                    </div>
                </div>
            @endforeach

            <button
                type="button"
                class="contacts-filter__clear"
            >
                <span>×</span>
                Clear
            </button>
        </section>

        <section class="contacts-table-shell">
            <div class="contacts-table-tools">
                <div class="contacts-table-tools__count">
                    Showing <strong>7</strong> of <strong>34,812</strong> contacts
                </div>

                <div class="contacts-table-tools__actions">
                    <span><i class="ti ti-columns-3"></i> Columns</span>
                    <span><i class="ti ti-download"></i> Export CSV</span>
                    <span><i class="ti ti-bookmark"></i> Save as segment</span>
                </div>
            </div>

            <div class="contacts-table">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="contacts-checkbox"></th>
                            <th>Contact</th>
                            <th>Organization</th>
                            <th>Country</th>
                            <th>Stage</th>
                            <th>Program / Interest</th>
                            <th>Source</th>
                            <th>Lessons</th>
                            <th>Score</th>
                            <th>Owner</th>
                            <th class="text-right">Last<br>Activity</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($contacts as $contact)
                            <tr>
                                <td><input type="checkbox" class="contacts-checkbox"></td>

                                <td>
                                    <div class="contacts-person">
                                        <span class="contacts-avatar {{ $contact['avatar'] }}">{{ $contact['initials'] }}</span>

                                        <div>
                                            <div class="contacts-person__name">
                                                {{ $contact['name'] }}

                                                @if (! empty($contact['merged']))
                                                    <span class="contacts-badge contacts-badge--merged">merged</span>
                                                @endif
                                            </div>

                                            <div class="contacts-person__email">{{ $contact['email'] }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $contact['organization'] }}</td>
                                <td>{{ $contact['country'] }}</td>

                                <td>
                                    <span class="contacts-stage {{ $contact['stage']['class'] }}">
                                        {{ $contact['stage']['label'] }}
                                    </span>
                                </td>

                                <td>{{ $contact['program'] }}</td>

                                <td>
                                    <span class="contacts-source">
                                        <i class="ti ti-link"></i>
                                        {{ $contact['source'] }}
                                    </span>
                                </td>

                                <td>
                                    <span class="contacts-lessons">
                                        <i class="ti ti-book"></i>
                                        {{ $contact['lessons'] }}
                                    </span>
                                </td>

                                <td>
                                    <span class="{{ $contact['score']['class'] }}">
                                        {{ $contact['score']['value'] }}
                                    </span>
                                </td>

                                <td>
                                    <div class="contacts-owner">
                                        <span class="contacts-owner__avatar {{ $contact['owner']['class'] }}">
                                            {{ $contact['owner']['initials'] }}
                                        </span>
                                        {{ $contact['owner']['name'] }}
                                    </div>
                                </td>

                                <td>
                                    <div class="contacts-activity">
                                        {{ $contact['last'] }}
                                        <i class="ti ti-trash"></i>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {!! view_render_event('admin.persons.index.content.after') !!}
</x-admin::layouts>
