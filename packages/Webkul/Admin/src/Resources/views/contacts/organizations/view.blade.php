<x-admin::layouts>
    <x-slot:title>
        IIT Jodhpur
    </x-slot>

    @php
        $metrics = [
            ['label' => 'Contacts', 'value' => '420'],
            ['label' => 'Engaged learners', 'value' => '96'],
            ['label' => 'Customers', 'value' => '28'],
            ['label' => 'Est. account value', 'value' => '$33,600'],
        ];

        $accountDetails = [
            ['label' => 'Type', 'value' => 'University'],
            ['label' => 'Country', 'value' => 'India'],
            ['label' => 'Licence status', 'value' => 'Licensed'],
            ['label' => 'Top program', 'value' => 'Single-Cell RNA-Seq'],
            ['label' => 'Account owner', 'value' => 'Ojasvi Dutta'],
        ];

        $notes = [
            'Active institutional licence (PGDP). 28 paying learners.',
            'Renewal Q3. Strong candidate for advanced-track expansion.',
        ];

        $people = [
            [
                'initials' => 'PN',
                'avatar' => 'organization-detail-person__avatar--red',
                'name' => 'Priya Nair',
                'subtitle' => 'PhD Scholar, Computational Biology · PhD',
                'stage' => 'Customer',
            ],
        ];

        $activities = [
            [
                'label' => 'R',
                'is_text' => true,
                'class' => 'organization-detail-activity__icon--teal',
                'title' => 'Institutional discussions logged',
                'meta' => 'Owner: Ojasvi Dutta',
                'time' => 'recent',
            ],
            [
                'icon' => 'fa-solid fa-bullhorn',
                'class' => 'organization-detail-activity__icon--blue',
                'title' => 'New registrations from this organization',
                'meta' => 'via Google Forms',
                'time' => 'this quarter',
            ],
            [
                'icon' => 'fa-solid fa-check',
                'class' => 'organization-detail-activity__icon--purple',
                'title' => 'Members completing portal lessons',
                'meta' => 'engagement signal',
                'time' => 'ongoing',
            ],
        ];
    @endphp

    <section class="organization-detail-page">
        <a
            href="{{ route('admin.contacts.organizations.index') }}"
            class="organization-detail-back"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Back to organizations
        </a>

        <section class="organization-detail-hero">
            <div class="organization-detail-hero__left">
                <span class="organization-detail-avatar organization-detail-avatar--red">
                    <i class="fa-solid fa-graduation-cap"></i>
                </span>

                <div>
                    <h1 class="organization-detail-hero__title">IIT Jodhpur</h1>

                    <div class="organization-detail-meta">
                        <span><i class="fa-solid fa-building-columns"></i> University</span>
                        <span><i class="fa-solid fa-location-dot"></i> India</span>
                        <span class="organization-detail-pill organization-detail-pill--licensed">Licensed</span>
                        <span class="organization-detail-owner">
                            Account owner:
                            <span class="organization-detail-owner__avatar organization-detail-owner__avatar--gold">OD</span>
                            Ojasvi Dutta
                        </span>
                    </div>
                </div>
            </div>

            <div class="organization-detail-hero__actions">
                <button type="button" class="organization-detail-btn">
                    <i class="fa-solid fa-user-plus"></i>
                    Reassign
                </button>

                <button type="button" class="organization-detail-btn">
                    <i class="fa-regular fa-file-lines"></i>
                    Licence proposal
                </button>
            </div>
        </section>

        <section class="organization-detail-metrics">
            @foreach ($metrics as $metric)
                <article class="organization-detail-metric">
                    <div class="organization-detail-metric__label">{{ $metric['label'] }}</div>
                    <div class="organization-detail-metric__value">{{ $metric['value'] }}</div>
                </article>
            @endforeach
        </section>

        <div class="organization-detail-grid">
            <div class="organization-detail-left">
                <article class="organization-detail-card">
                    <div class="organization-detail-card__head">
                        <h2>Account</h2>

                        <button type="button" class="organization-detail-edit">
                            <i class="fa-regular fa-pen-to-square"></i>
                            Edit
                        </button>
                    </div>

                    <dl class="organization-detail-rows">
                        @foreach ($accountDetails as $row)
                            <div class="organization-detail-row">
                                <dt>{{ $row['label'] }}</dt>
                                <dd>{{ $row['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </article>

                <article class="organization-detail-card">
                    <div class="organization-detail-card__head">
                        <h2>
                            <i class="fa-regular fa-note-sticky"></i>
                            Account notes & next steps
                        </h2>
                    </div>

                    <ul class="organization-detail-notes">
                        @foreach ($notes as $note)
                            <li>
                                <input type="checkbox" disabled>
                                <span>{{ $note }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <button type="button" class="organization-detail-add-note">
                        <i class="fa-solid fa-plus"></i>
                        Add note
                    </button>
                </article>
            </div>

            <div class="organization-detail-right">
                <article class="organization-detail-card">
                    <div class="organization-detail-card__head">
                        <h2>People at this organization</h2>
                        <span class="organization-detail-count">{{ count($people) }} shown</span>
                    </div>

                    <ul class="organization-detail-people">
                        @foreach ($people as $person)
                            <li>
                                <div class="organization-detail-person">
                                    <span class="organization-detail-person__avatar {{ $person['avatar'] }}">
                                        {{ $person['initials'] }}
                                    </span>

                                    <div>
                                        <strong>{{ $person['name'] }}</strong>
                                        <p>{{ $person['subtitle'] }}</p>
                                    </div>
                                </div>

                                <span class="organization-detail-pill organization-detail-pill--customer">
                                    {{ $person['stage'] }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </article>

                <article class="organization-detail-card">
                    <div class="organization-detail-card__head">
                        <h2>
                            <i class="fa-solid fa-chart-line"></i>
                            Account activity
                        </h2>
                    </div>

                    <ul class="organization-detail-activity">
                        @foreach ($activities as $activity)
                            <li class="organization-detail-activity__item">
                                <span class="organization-detail-activity__icon {{ $activity['class'] }}">
                                    @if (! empty($activity['is_text']))
                                        {{ $activity['label'] }}
                                    @else
                                        <i class="{{ $activity['icon'] }}"></i>
                                    @endif
                                </span>

                                <div>
                                    <strong>{{ $activity['title'] }}</strong>
                                    <p>{{ $activity['meta'] }}</p>
                                </div>

                                <time>{{ $activity['time'] }}</time>
                            </li>
                        @endforeach
                    </ul>
                </article>
            </div>
        </div>

        
    </section>
</x-admin::layouts>
