<x-admin::layouts>
    <x-slot:title>
        Reports &amp; analytics
    </x-slot>

    @php
        $filters = [
            ['label' => 'Year', 'value' => '2026'],
            ['label' => 'From month', 'value' => 'Jan'],
            ['label' => 'To month', 'value' => 'Dec'],
            ['label' => 'Organization', 'value' => 'All organizations'],
            ['label' => 'Education', 'value' => 'All levels'],
            ['label' => 'Source', 'value' => 'All sources'],
            ['label' => 'Program', 'value' => 'All programs'],
        ];

        $metrics = [
            ['icon' => 'ti ti-users', 'label' => 'LEADS IN RANGE', 'value' => '3,682', 'note' => 'Jan–Dec 2026'],
            ['icon' => 'ti ti-flame', 'label' => 'ENGAGED RATE', 'value' => '17.8%', 'note' => '655 learners ≥1 lesson'],
            ['icon' => 'ti ti-award', 'label' => 'CONVERSION', 'value' => '3.9%', 'note' => '144 customers'],
            ['icon' => 'ti ti-calendar-stats', 'label' => 'AVG / MONTH', 'value' => '614', 'note' => '6 months in range'],
        ];

        $months = [
            ['label' => 'Jan', 'value' => '540', 'class' => 'reports-month__bar--jan'],
            ['label' => 'Feb', 'value' => '610', 'class' => 'reports-month__bar--feb'],
            ['label' => 'Mar', 'value' => '580', 'class' => 'reports-month__bar--mar'],
            ['label' => 'Apr', 'value' => '640', 'class' => 'reports-month__bar--apr'],
            ['label' => 'May', 'value' => '700', 'class' => 'reports-month__bar--may'],
            ['label' => 'Jun', 'value' => '812', 'class' => 'reports-month__bar--jun'],
        ];

        $organizations = [
            ['label' => 'IIT Jodhpur', 'value' => '225', 'class' => 'reports-width--100'],
            ['label' => 'AIIMS New Delhi', 'value' => '158', 'class' => 'reports-width--70'],
            ['label' => 'Tehran Univ. of Medical Sciences', 'value' => '133', 'class' => 'reports-width--59'],
            ['label' => 'University of Lagos', 'value' => '110', 'class' => 'reports-width--49'],
            ['label' => 'Cairo University', 'value' => '96', 'class' => 'reports-width--43'],
            ['label' => 'McGill University', 'value' => '74', 'class' => 'reports-width--33'],
            ['label' => 'NIBMG Kalyani', 'value' => '63', 'class' => 'reports-width--28'],
            ['label' => 'University of Delaware', 'value' => '55', 'class' => 'reports-width--24'],
        ];

        $education = [
            ['label' => 'Masters', 'value' => '1,252', 'width_class' => 'reports-width--100', 'color_class' => 'reports-bar--blue'],
            ['label' => 'PhD', 'value' => '994', 'width_class' => 'reports-width--79', 'color_class' => 'reports-bar--purple'],
            ['label' => 'Undergraduate', 'value' => '773', 'width_class' => 'reports-width--62', 'color_class' => 'reports-bar--green'],
            ['label' => 'Faculty', 'value' => '442', 'width_class' => 'reports-width--35', 'color_class' => 'reports-bar--green'],
            ['label' => 'Industry', 'value' => '221', 'width_class' => 'reports-width--18', 'color_class' => 'reports-bar--gold'],
        ];
    @endphp

    {!! view_render_event('admin.report.index.content.before') !!}

    <div class="reports-page">
        <section class="reports-hero">
            <h1>Reports &amp; analytics</h1>
            <p>Slice the unified data by year, month range, organization, education, source, or program — every chart updates live.</p>
        </section>

        <section class="reports-filters">
            @foreach ($filters as $filter)
                <div class="reports-filter">
                    <label>{{ $filter['label'] }}</label>
                    <div class="reports-filter__select">
                        <select>
                            <option>{{ $filter['value'] }}</option>
                        </select>
                    </div>
                </div>
            @endforeach

            <button
                type="button"
                class="reports-filter__reset"
            >
                <span>×</span>
                Reset
            </button>
        </section>

        <section class="reports-metrics">
            @foreach ($metrics as $metric)
                <article class="reports-metric">
                    <div class="reports-metric__label">
                        <i class="{{ $metric['icon'] }}"></i>
                        {{ $metric['label'] }}
                    </div>

                    <div class="reports-metric__value">{{ $metric['value'] }}</div>
                    <div class="reports-metric__note">{{ $metric['note'] }}</div>
                </article>
            @endforeach
        </section>

        <section class="reports-chart-card reports-month-card">
            <div class="reports-card-header">
                <h2>Leads by month</h2>
                <span>Jan–Dec 2026</span>
            </div>

            <div class="reports-month-bars">
                @foreach ($months as $month)
                    <div class="reports-month">
                        <span>{{ $month['value'] }}</span>
                        <div class="{{ $month['class'] }}"></div>
                        <p>{{ $month['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="reports-grid">
            <article class="reports-chart-card">
                <div class="reports-card-header">
                    <h2>Top organizations by leads</h2>
                    <span>top 8</span>
                </div>

                <div class="reports-horizontal-bars reports-horizontal-bars--organizations">
                    @foreach ($organizations as $organization)
                        <div class="reports-horizontal-row">
                            <span>{{ $organization['label'] }}</span>
                            <div>
                                <i class="{{ $organization['class'] }}"></i>
                            </div>
                            <strong>{{ $organization['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="reports-chart-card">
                <div class="reports-card-header">
                    <h2>Leads by education</h2>
                    <span>background</span>
                </div>

                <div class="reports-horizontal-bars">
                    @foreach ($education as $item)
                        <div class="reports-horizontal-row">
                            <span>{{ $item['label'] }}</span>
                            <div>
                                <i class="{{ $item['color_class'] }} {{ $item['width_class'] }}"></i>
                            </div>
                            <strong>{{ $item['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>
    </div>

    {!! view_render_event('admin.report.index.content.after') !!}
</x-admin::layouts>
