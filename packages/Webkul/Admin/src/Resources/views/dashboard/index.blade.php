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
            ['icon' => 'fa-solid fa-users', 'label' => 'LEADS IN RANGE', 'value' => '3,682', 'note' => 'Jan-Dec 2026', 'class' => 'reports-metric--blue'],
            ['icon' => 'fa-solid fa-fire-flame-curved', 'label' => 'ENGAGED RATE', 'value' => '17.8%', 'note' => '655 learners >=1 lesson', 'class' => 'reports-metric--green'],
            ['icon' => 'fa-solid fa-award', 'label' => 'CONVERSION', 'value' => '3.9%', 'note' => '144 customers', 'class' => 'reports-metric--purple'],
            ['icon' => 'fa-regular fa-calendar-days', 'label' => 'AVG / MONTH', 'value' => '614', 'note' => '6 months in range', 'class' => 'reports-metric--gold'],
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

        $sources = [
            ['label' => 'Google Form', 'value' => '1,510', 'width_class' => 'reports-width--100', 'color_class' => 'reports-bar--blue'],
            ['label' => 'Portal', 'value' => '1,031', 'width_class' => 'reports-width--68', 'color_class' => 'reports-bar--purple'],
            ['label' => 'Zoho import', 'value' => '884', 'width_class' => 'reports-width--59', 'color_class' => 'reports-bar--slate'],
            ['label' => 'Referral', 'value' => '258', 'width_class' => 'reports-width--17', 'color_class' => 'reports-bar--green'],
        ];

        $programs = [
            ['label' => 'RNA-Seq Data Analysis', 'value' => '736', 'width_class' => 'reports-width--100'],
            ['label' => 'Sample to Sequencer (NGS)', 'value' => '515', 'width_class' => 'reports-width--70'],
            ['label' => 'AI in Drug Discovery', 'value' => '398', 'width_class' => 'reports-width--54'],
            ['label' => 'Clinical Genomics', 'value' => '350', 'width_class' => 'reports-width--48'],
            ['label' => 'Single-Cell RNA-Seq', 'value' => '272', 'width_class' => 'reports-width--37'],
            ['label' => 'Metagenomics', 'value' => '199', 'width_class' => 'reports-width--27'],
            ['label' => 'Machine Learning for Omics', 'value' => '184', 'width_class' => 'reports-width--25'],
            ['label' => 'Cheminformatics', 'value' => '147', 'width_class' => 'reports-width--20'],
        ];

        $countries = [
            ['label' => 'India', 'value' => '1,988', 'width_class' => 'reports-width--100'],
            ['label' => 'Others', 'value' => '589', 'width_class' => 'reports-width--30'],
            ['label' => 'United States', 'value' => '368', 'width_class' => 'reports-width--19'],
            ['label' => 'Nigeria', 'value' => '184', 'width_class' => 'reports-width--9'],
            ['label' => 'Pakistan', 'value' => '169', 'width_class' => 'reports-width--9'],
            ['label' => 'Iran', 'value' => '147', 'width_class' => 'reports-width--7'],
            ['label' => 'Egypt', 'value' => '125', 'width_class' => 'reports-width--6'],
            ['label' => 'Canada', 'value' => '110', 'width_class' => 'reports-width--6'],
        ];

        $lessons = [
            ['label' => 'RNA-Seq: Read Alignment', 'value' => '252', 'width_class' => 'reports-width--100'],
            ['label' => 'NGS: Library Prep QC', 'value' => '204', 'width_class' => 'reports-width--81'],
            ['label' => 'scRNA-Seq: Pseudotime (Monocle)', 'value' => '173', 'width_class' => 'reports-width--69'],
            ['label' => 'Clinical Genomics: VCF Basics', 'value' => '157', 'width_class' => 'reports-width--62'],
            ['label' => 'AI Drug Discovery: Intro to RDKit', 'value' => '138', 'width_class' => 'reports-width--55'],
            ['label' => 'ML for Omics: Model Validation', 'value' => '113', 'width_class' => 'reports-width--45'],
            ['label' => 'Metagenomics: Taxonomic Profiling', 'value' => '94', 'width_class' => 'reports-width--37'],
            ['label' => 'Cheminformatics: Molecular Descriptors', 'value' => '79', 'width_class' => 'reports-width--31'],
        ];

        $completionRates = [
            ['label' => 'RNA-Seq Data Analysis', 'value' => '72', 'width_class' => 'reports-width--72', 'color_class' => 'reports-bar--green'],
            ['label' => 'Single-Cell RNA-Seq', 'value' => '68', 'width_class' => 'reports-width--68', 'color_class' => 'reports-bar--green'],
            ['label' => 'Machine Learning for Omics', 'value' => '64', 'width_class' => 'reports-width--64', 'color_class' => 'reports-bar--green'],
            ['label' => 'Clinical Genomics', 'value' => '58', 'width_class' => 'reports-width--58', 'color_class' => 'reports-bar--gold'],
            ['label' => 'Sample to Sequencer (NGS)', 'value' => '55', 'width_class' => 'reports-width--55', 'color_class' => 'reports-bar--gold'],
            ['label' => 'AI in Drug Discovery', 'value' => '49', 'width_class' => 'reports-width--49', 'color_class' => 'reports-bar--muted'],
            ['label' => 'Metagenomics', 'value' => '46', 'width_class' => 'reports-width--46', 'color_class' => 'reports-bar--muted'],
            ['label' => 'Cheminformatics', 'value' => '41', 'width_class' => 'reports-width--41', 'color_class' => 'reports-bar--muted'],
        ];

        $years = [
            ['label' => '2023', 'value' => '6,200', 'class' => 'reports-year__bar--2023'],
            ['label' => '2024', 'value' => '9,800', 'class' => 'reports-year__bar--2024'],
            ['label' => '2025', 'value' => '12,100', 'class' => 'reports-year__bar--2025'],
            ['label' => '2026', 'value' => '8,712', 'class' => 'reports-year__bar--2026'],
        ];
    @endphp

    {!! view_render_event('admin.dashboard.index.content.before') !!}

    <div class="reports-page">
        <section class="reports-hero">
            <h1>Dashboard</h1>
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
                <article class="reports-metric {{ $metric['class'] }}">
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
                <span>Jan-Dec 2026</span>
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

        <section class="reports-grid">
            <article class="reports-chart-card">
                <div class="reports-card-header">
                    <h2>
                        <i class="fa-solid fa-link"></i>
                        Leads by source
                    </h2>
                </div>

                <div class="reports-horizontal-bars reports-source-bars">
                    @foreach ($sources as $source)
                        <div class="reports-horizontal-row">
                            <span>{{ $source['label'] }}</span>

                            <div>
                                <i class="{{ $source['color_class'] }} {{ $source['width_class'] }}"></i>
                            </div>

                            <strong>{{ $source['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="reports-chart-card">
                <div class="reports-card-header">
                    <h2>
                        <i class="fa-solid fa-filter"></i>
                        Lifecycle funnel
                    </h2>
                </div>

                <div class="reports-funnel">
                    <div class="reports-funnel__bar reports-funnel__bar--contacts">
                        All contacts - 3,682
                    </div>

                    <div class="reports-funnel__bar reports-funnel__bar--engaged">
                        Engaged - 655
                    </div>

                    <div class="reports-funnel__bar reports-funnel__bar--customers">
                        Customers - 144
                    </div>

                    <p>Lead → Engaged 18% → Customer 22%</p>
                </div>
            </article>
        </section>

        <section class="reports-grid">
            <article class="reports-chart-card">
                <div class="reports-card-header">
                    <h2>
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        Top programs by leads
                    </h2>
                </div>

                <div class="reports-horizontal-bars">
                    @foreach ($programs as $program)
                        <div class="reports-horizontal-row">
                            <span>{{ $program['label'] }}</span>

                            <div>
                                <i class="reports-bar--purple {{ $program['width_class'] }}"></i>
                            </div>

                            <strong>{{ $program['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="reports-chart-card">
                <div class="reports-card-header">
                    <h2>
                        <i class="fa-solid fa-location-dot"></i>
                        Leads by country
                    </h2>
                </div>

                <div class="reports-horizontal-bars">
                    @foreach ($countries as $country)
                        <div class="reports-horizontal-row">
                            <span>{{ $country['label'] }}</span>

                            <div>
                                <i class="reports-bar--blue {{ $country['width_class'] }}"></i>
                            </div>

                            <strong>{{ $country['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="reports-grid">
            <article class="reports-chart-card">
                <div class="reports-card-header">
                    <h2>Top completed lessons</h2>
                    <span>most finishes</span>
                </div>

                <div class="reports-horizontal-bars">
                    @foreach ($lessons as $lesson)
                        <div class="reports-horizontal-row">
                            <span>{{ $lesson['label'] }}</span>

                            <div>
                                <i class="reports-bar--purple {{ $lesson['width_class'] }}"></i>
                            </div>

                            <strong>{{ $lesson['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="reports-chart-card">
                <div class="reports-card-header">
                    <h2>Course completion rate</h2>
                    <span>% who finish</span>
                </div>

                <div class="reports-horizontal-bars">
                    @foreach ($completionRates as $rate)
                        <div class="reports-horizontal-row">
                            <span>{{ $rate['label'] }}</span>

                            <div>
                                <i class="{{ $rate['color_class'] }} {{ $rate['width_class'] }}"></i>
                            </div>

                            <strong>{{ $rate['value'] }}</strong>
                        </div>
                    @endforeach
                </div>

                <p class="reports-card-note">
                    Share of enrolled learners who complete the course. A low rate flags where the curriculum or onboarding needs work.
                </p>
            </article>
        </section>

        <section class="reports-chart-card reports-year-card">
            <div class="reports-card-header">
                <h2>Year-over-year — total leads</h2>
                <span>all sources</span>
            </div>

            <div class="reports-year-bars">
                @foreach ($years as $year)
                    <div class="reports-year">
                        <span>{{ $year['value'] }}</span>
                        <div class="{{ $year['class'] }}"></div>
                        <p>{{ $year['label'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="reports-year-note">2026 is year-to-date. Selected year is highlighted.</div>
        </section>

        <div class="reports-export-actions">
            <button type="button">
                <i class="fa-solid fa-download"></i>
                Export this view (CSV)
            </button>

            <button type="button">
                <i class="fa-regular fa-file-lines"></i>
                Export PDF report
            </button>
        </div>
    </div>

    {!! view_render_event('admin.dashboard.index.content.after') !!}
</x-admin::layouts>
