<x-admin::layouts>
    <x-slot:title>
        Organizations
    </x-slot>
@php
        $metrics = [
            ['icon' => 'ti ti-building', 'label' => 'ORGANIZATIONS', 'value' => '1,240', 'note' => 'across 84 countries'],
            ['icon' => 'ti ti-school', 'label' => 'UNIVERSITIES', 'value' => '980', 'note' => '79% of total'],
            ['icon' => 'ti ti-flame', 'label' => 'WITH ACTIVE LEARNERS', 'value' => '312', 'note' => '&ge;1 engaged contact'],
            ['icon' => 'ti ti-world-dollar', 'label' => 'LICENSE PROSPECTS', 'value' => '47', 'note' => 'high engagement, no license', 'prospect' => true],
        ];

        $filters = [
            ['label' => 'Type', 'value' => 'All types'],
            ['label' => 'Country', 'value' => 'All countries'],
            ['label' => 'License status', 'value' => 'Any'],
            ['label' => 'Sort by', 'value' => 'Engaged learners ↓'],
        ];

        $organizations = [
            ['icon' => 'ti ti-school', 'avatar' => 'organization-avatar--red', 'name' => 'IIT Jodhpur', 'type' => 'University', 'country' => 'India', 'contacts' => '420', 'engaged' => '96', 'customers' => '28', 'owner' => ['initials' => 'OD', 'name' => 'Ojasvi Dutta', 'class' => 'organization-owner__avatar--gold'], 'license' => ['label' => 'Licensed', 'class' => 'organization-license--licensed']],
            ['icon' => 'ti ti-school', 'avatar' => 'organization-avatar--blue', 'name' => 'AIIMS New Delhi', 'type' => 'University', 'country' => 'India', 'contacts' => '210', 'engaged' => '40', 'customers' => '9', 'owner' => ['initials' => 'MM', 'name' => 'Mohit M.', 'class' => 'organization-owner__avatar--green'], 'license' => ['label' => 'Prospect', 'class' => 'organization-license--prospect']],
            ['icon' => 'ti ti-school', 'avatar' => 'organization-avatar--gold', 'name' => 'Tehran Univ. of Medical Sciences', 'type' => 'University', 'country' => 'Iran', 'contacts' => '180', 'engaged' => '22', 'customers' => '4', 'owner' => ['initials' => '', 'name' => 'Unassigned', 'class' => 'organization-owner__avatar--orange'], 'license' => ['label' => 'Prospect', 'class' => 'organization-license--prospect']],
            ['icon' => 'ti ti-school', 'avatar' => 'organization-avatar--gold', 'name' => 'University of Lagos', 'type' => 'University', 'country' => 'Nigeria', 'contacts' => '140', 'engaged' => '31', 'customers' => '6', 'owner' => ['initials' => 'MM', 'name' => 'Mohit M.', 'class' => 'organization-owner__avatar--green'], 'license' => ['label' => 'Prospect', 'class' => 'organization-license--prospect']],
            ['icon' => 'ti ti-school', 'avatar' => 'organization-avatar--green', 'name' => 'Cairo University', 'type' => 'University', 'country' => 'Egypt', 'contacts' => '110', 'engaged' => '27', 'customers' => '5', 'owner' => ['initials' => 'OD', 'name' => 'Ojasvi Dutta', 'class' => 'organization-owner__avatar--gold'], 'license' => ['label' => 'Prospect', 'class' => 'organization-license--prospect']],
            ['icon' => 'ti ti-school', 'avatar' => 'organization-avatar--green', 'name' => 'McGill University', 'type' => 'University', 'country' => 'Canada', 'contacts' => '90', 'engaged' => '25', 'customers' => '11', 'owner' => ['initials' => 'HS', 'name' => 'Harshita Sharma', 'class' => 'organization-owner__avatar--gold'], 'license' => ['label' => '—', 'class' => 'organization-license--none']],
            ['icon' => 'ti ti-building-bank', 'avatar' => 'organization-avatar--blue', 'name' => 'NIBMG Kalyani', 'type' => 'Institute', 'country' => 'India', 'contacts' => '75', 'engaged' => '9', 'customers' => '2', 'owner' => ['initials' => '', 'name' => 'Unassigned', 'class' => 'organization-owner__avatar--orange'], 'license' => ['label' => '—', 'class' => 'organization-license--none']],
            ['icon' => 'ti ti-school', 'avatar' => 'organization-avatar--purple', 'name' => 'University of Delaware', 'type' => 'University', 'country' => 'United States', 'contacts' => '65', 'engaged' => '14', 'customers' => '3', 'owner' => ['initials' => 'HS', 'name' => 'Harshita Sharma', 'class' => 'organization-owner__avatar--gold'], 'license' => ['label' => '—', 'class' => 'organization-license--none']],
            ['icon' => 'ti ti-school', 'avatar' => 'organization-avatar--red', 'name' => 'Aga Khan University', 'type' => 'University', 'country' => 'Pakistan', 'contacts' => '60', 'engaged' => '8', 'customers' => '1', 'owner' => ['initials' => '', 'name' => 'Unassigned', 'class' => 'organization-owner__avatar--orange'], 'license' => ['label' => '—', 'class' => 'organization-license--none']],
            ['icon' => 'ti ti-building-bank', 'avatar' => 'organization-avatar--red', 'name' => 'ICGEB', 'type' => 'Institute', 'country' => 'India', 'contacts' => '55', 'engaged' => '12', 'customers' => '3', 'owner' => ['initials' => 'MM', 'name' => 'Mohit M.', 'class' => 'organization-owner__avatar--green'], 'license' => ['label' => 'Partner', 'class' => 'organization-license--partner']],
            ['icon' => 'ti ti-building', 'avatar' => 'organization-avatar--purple', 'name' => 'Premas Biotech', 'type' => 'Company', 'country' => 'India', 'contacts' => '18', 'engaged' => '0', 'customers' => '0', 'owner' => ['initials' => 'MM', 'name' => 'Mohit M.', 'class' => 'organization-owner__avatar--green'], 'license' => ['label' => 'Partner', 'class' => 'organization-license--partner']],
            ['icon' => 'ti ti-building', 'avatar' => 'organization-avatar--blue', 'name' => 'Geneyx', 'type' => 'Company', 'country' => 'Israel', 'contacts' => '12', 'engaged' => '0', 'customers' => '0', 'owner' => ['initials' => 'MM', 'name' => 'Mohit M.', 'class' => 'organization-owner__avatar--green'], 'license' => ['label' => 'Partner', 'class' => 'organization-license--partner']],
        ];
    @endphp

    {!! view_render_event('admin.organizations.index.content.before') !!}

    <div class="organizations-page">
        <section class="organizations-hero">
            <h1>Organizations</h1>
            <p>Universities, institutes and partner companies — rolled up from contact affiliations, for institutional licensing and outreach.</p>
        </section>

        <section class="organizations-metrics">
            @foreach ($metrics as $metric)
                <article class="organizations-metric {{ ! empty($metric['prospect']) ? 'organizations-metric--prospect' : '' }}">
                    <div class="organizations-metric__label">
                        <i class="{{ $metric['icon'] }}"></i>
                        {{ $metric['label'] }}
                    </div>

                    <div class="organizations-metric__value">{{ $metric['value'] }}</div>
                    <div class="organizations-metric__note">{!! $metric['note'] !!}</div>
                </article>
            @endforeach
        </section>

        <section class="organizations-filters">
            @foreach ($filters as $filter)
                <div class="organizations-filter">
                    <label>{{ $filter['label'] }}</label>
                    <div class="organizations-filter__select">
                        <select>
                            <option>{{ $filter['value'] }}</option>
                        </select>
                    </div>
                </div>
            @endforeach

            <button
                type="button"
                class="organizations-filter__clear"
            >
                <span>×</span>
                Clear
            </button>
        </section>

        <section class="organizations-table-shell">
            <div class="organizations-table-tools">
                <div class="organizations-table-tools__count">
                    Showing <strong>12</strong> of <strong>1,240</strong> organizations
                </div>

                <div class="organizations-export">
                    <i class="ti ti-download"></i>
                    Export CSV
                </div>
            </div>

            <div class="organizations-table">
                <table>
                    <thead>
                        <tr>
                            <th>Organization</th>
                            <th>Country</th>
                            <th>Contacts</th>
                            <th>Engaged</th>
                            <th>Customers</th>
                            <th>Owner</th>
                            <th>License</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($organizations as $organization)
                            <tr>
                                <td>
                                    <div class="organization-name">
                                        <span class="organization-avatar {{ $organization['avatar'] }}">
                                            <i class="{{ $organization['icon'] }}"></i>
                                        </span>

                                        <div>
                                            <div class="organization-title">{{ $organization['name'] }}</div>
                                            <div class="organization-type">{{ $organization['type'] }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $organization['country'] }}</td>

                                <td>
                                    <span class="organization-count">{{ $organization['contacts'] }}</span>
                                </td>

                                <td>
                                    <span class="organization-stat organization-stat--engaged">
                                        <i class="ti ti-flame"></i>
                                        {{ $organization['engaged'] }}
                                    </span>
                                </td>

                                <td>
                                    <span class="organization-stat organization-stat--customers">
                                        <i class="ti ti-user-check"></i>
                                        {{ $organization['customers'] }}
                                    </span>
                                </td>

                                <td>
                                    <span class="organization-owner">
                                        <span class="organization-owner__avatar {{ $organization['owner']['class'] }}">
                                            @if ($organization['owner']['initials'])
                                                {{ $organization['owner']['initials'] }}
                                            @else
                                                <i class="ti ti-user-x"></i>
                                            @endif
                                        </span>

                                        {{ $organization['owner']['name'] }}
                                    </span>
                                </td>

                                <td>
                                    <span class="organization-license {{ $organization['license']['class'] }}">
                                        {{ $organization['license']['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {!! view_render_event('admin.organizations.index.content.after') !!}
</x-admin::layouts>
