<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.dashboard.index.title')
    </x-slot>
@php
        $pipelineSteps = [
            ['label' => 'Capture Lead', 'color' => 'crm-step--blue'],
            ['label' => 'Qualify', 'color' => 'crm-step--purple'],
            ['label' => 'Create Opportunity', 'color' => 'crm-step--teal'],
            ['label' => 'Quote', 'color' => 'crm-step--green'],
            ['label' => 'Close Deal', 'color' => 'crm-step--blue-dark'],
            ['label' => 'Follow Up', 'color' => 'crm-step--grey'],
        ];

    @endphp

    {!! view_render_event('admin.dashboard.index.content.before') !!}

    <div class="min-h-screen rounded-2xl bg-[var(--bg)] p-4 text-[var(--ink)] dark:bg-gray-950 dark:text-gray-100 max-sm:p-2">
        <section class="overflow-hidden rounded-2xl crm-section">
            <div class="flex items-start justify-between gap-6 max-lg:flex-col">
                <div class="max-w-3xl">
                    <p class="crm-section__eyebrow mb-3 inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                        CRM Operating Guide
                    </p>

                    <h1 class="text-3xl font-bold leading-tight max-sm:text-2xl">
                        How the OmicsLogic CRM works
                    </h1>

                    <p class="crm-section__muted mt-3 max-w-2xl text-sm leading-6">
                    This CRM exists to solve one problem: the same learner used to live in a dozen disconnected Google Sheets, portal logins, and an old Zoho export. Here, all of that becomes<b>one clean record per person</b>
                                   </p>
                                   <p class="crm-section__muted mt-3 max-w-2xl text-sm leading-6">
                                   — then everything else (orgs, segments, reports, deals) reads from that single source of truth. Below is what each section does, how data flows between them, and the two “stage” systems explained.                                   </p>
                </div>

                <!-- <div class="crm-section__metric grid min-w-[210px] gap-2 rounded-2xl p-4 backdrop-blur">
                    <div class="crm-section__muted flex items-center justify-between text-xs">
                        <span>Pipeline Health</span>
                        <span>Live CRM</span>
                    </div>

                    <div class="crm-section__progress-track h-2 overflow-hidden rounded-full">
                        <div class="crm-section__progress-bar h-full w-4/5 rounded-full"></div>
                    </div>

                    <p class="text-2xl font-bold">80%</p>
                    <p class="crm-section__muted text-xs">Lead to close process coverage</p>
                </div> -->
            </div>

            <!-- <div class="crm-section__pipeline-card mt-6 rounded-2xl p-5 dark:bg-gray-900 dark:text-gray-100">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    @foreach ($pipelineSteps as $step)
                        <div class="flex min-w-[120px] flex-1 items-center gap-3">
                            <span class="{{ $step['color'] }} flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white">
                                {{ $loop->iteration }}
                            </span>

                            <span class="text-sm font-semibold">
                                {{ $step['label'] }}
                            </span>
                        </div>

                        @unless ($loop->last)
                            <span class="crm-section__connector h-px w-8 max-xl:hidden dark:bg-gray-700"></span>
                        @endunless
                    @endforeach
                </div>
            </div> -->
        </section>

        <div class="gsec">
            <i class="gsec__icon ti ti-route"></i>
            The roadmap — how it all connects
        </div>

        <section class="crm-roadmap">
            <svg
                class="crm-roadmap__svg"
                viewBox="0 0 980 360"
                xmlns="http://www.w3.org/2000/svg"
            >
                <defs>
                    <marker
                        id="ah"
                        markerWidth="9"
                        markerHeight="9"
                        refX="7"
                        refY="4.5"
                        orient="auto"
                    >
                        <path
                            class="arrow-fill"
                            d="M0,0 L9,4.5 L0,9 Z"
                        ></path>
                    </marker>
                </defs>

                <text x="80" y="22" text-anchor="middle" class="col-t">1 · SOURCES</text>
                <text x="330" y="22" text-anchor="middle" class="col-t">2 · INGEST &amp; CLEAN</text>
                <text x="600" y="22" text-anchor="middle" class="col-t">3 · UNIFIED RECORD</text>
                <text x="860" y="22" text-anchor="middle" class="col-t">4 · ACT ON IT</text>

                <g>
                    <rect x="16" y="60" width="128" height="48" rx="9" fill="#1F6FB2"></rect>
                    <text x="80" y="81" text-anchor="middle" class="lab">Google Forms</text>
                    <text x="80" y="97" text-anchor="middle" class="sub2">14 reg. forms</text>
                </g>

                <g>
                    <rect x="16" y="130" width="128" height="48" rx="9" fill="#6c4bb6"></rect>
                    <text x="80" y="151" text-anchor="middle" class="lab">OmicsLogic Portal</text>
                    <text x="80" y="167" text-anchor="middle" class="sub2">lessons · ratings</text>
                </g>

                <g>
                    <rect x="16" y="200" width="128" height="48" rx="9" fill="#0f8a78"></rect>
                    <text x="80" y="221" text-anchor="middle" class="lab">CSV / Excel</text>
                    <text x="80" y="237" text-anchor="middle" class="sub2">backfills</text>
                </g>

                <g>
                    <rect x="16" y="270" width="128" height="48" rx="9" fill="#62707d"></rect>
                    <text x="80" y="291" text-anchor="middle" class="lab">Zoho (legacy)</text>
                    <text x="80" y="307" text-anchor="middle" class="sub2">35,112 rows</text>
                </g>

                <path class="flowln" d="M148,84 C190,84 200,150 248,150"></path>
                <path class="flowln" d="M148,154 C190,154 200,150 248,150"></path>
                <path class="flowln" d="M148,224 C190,224 200,150 248,150"></path>
                <path class="flowln" d="M148,294 C190,294 200,150 248,150"></path>

                <rect x="250" y="96" width="160" height="108" rx="11" fill="#fff8f1" stroke="#f0c9a6"></rect>
                <text x="330" y="120" text-anchor="middle" class="nb">Connectors + Imports</text>
                <text x="330" y="138" text-anchor="middle" class="ns">field-map · dedup engine</text>
                <text x="330" y="160" text-anchor="middle" class="ns">exact email → auto-merge</text>
                <text x="330" y="175" text-anchor="middle" class="ns">0.70–0.95 → Merge review</text>
                <text x="330" y="194" text-anchor="middle" class="ns ns-strong">human checks the gray zone</text>

                <path class="flowln" d="M410,150 C450,150 460,150 500,150"></path>

                <rect x="502" y="86" width="196" height="128" rx="12" fill="#12476F"></rect>
                <text x="600" y="112" text-anchor="middle" class="lab lab-lg">ONE CONTACT RECORD</text>
                <text x="600" y="132" text-anchor="middle" class="sub2">identity · source · timeline</text>
                <text x="600" y="148" text-anchor="middle" class="sub2">lessons · score · owner</text>

                <rect x="520" y="160" width="76" height="40" rx="7" fill="#1F6FB2"></rect>
                <text x="558" y="176" text-anchor="middle" class="sub2 sub2-strong">Lifecycle</text>
                <text x="558" y="190" text-anchor="middle" class="sub2">stage</text>

                <rect x="604" y="160" width="76" height="40" rx="7" fill="#2d7d46"></rect>
                <text x="642" y="176" text-anchor="middle" class="sub2 sub2-strong">Sales</text>
                <text x="642" y="190" text-anchor="middle" class="sub2">pipeline</text>

                <path class="flowln" d="M600,214 C600,250 600,250 600,276"></path>
                <rect x="500" y="278" width="200" height="42" rx="9" fill="#0f8a78"></rect>
                <text x="600" y="299" text-anchor="middle" class="lab">Organizations (roll-up)</text>
                <text x="600" y="313" text-anchor="middle" class="sub2">same people, grouped by institution</text>

                <path class="flowln" d="M698,150 C740,150 760,76 792,76"></path>
                <rect x="794" y="60" width="150" height="34" rx="8" fill="#1c2530"></rect>
                <text x="869" y="82" text-anchor="middle" class="lab lab-sm">Segments</text>

                <path class="flowln" d="M698,150 C740,150 760,134 792,134"></path>
                <rect x="794" y="118" width="150" height="34" rx="8" fill="#1c2530"></rect>
                <text x="869" y="140" text-anchor="middle" class="lab lab-sm">Reports</text>

                <path class="flowln" d="M698,150 C740,150 760,192 792,192"></path>
                <rect x="794" y="176" width="150" height="34" rx="8" fill="#1c2530"></rect>
                <text x="869" y="198" text-anchor="middle" class="lab lab-sm">Campaigns</text>

                <rect x="250" y="232" width="448" height="34" rx="8" fill="#eef1f4" stroke="#e3e8ee"></rect>
                <text x="474" y="254" text-anchor="middle" class="ns ns-audit">Audit log + Settings govern every step above (who did what · dedup rules · privacy)</text>
            </svg>
        </section>
        <section class="mt-5 grid grid-cols-[1.2fr_0.8fr] gap-4 max-xl:grid-cols-1">
            <div class="gmap-legend">
                <span><span class="sw sw--sources"></span> Sources feed in</span>
                <span><span class="sw sw--cleaned"></span> Cleaned &amp; deduplicated</span>
                <span><span class="sw sw--record"></span> Unified record (the core)</span>
                <span><span class="sw sw--action"></span> Where you take action</span>
                <span><span class="sw sw--governed"></span> Governed by Audit + Settings</span>
            </div>

            <div class="gsteps">
                <div class="gstep">
                    <span class="gn gn--blue">1</span>
                    <div>
                        <b>Data comes in.</b> People register through forms, take lessons on the portal, or get uploaded from old files. All of it lands in one place automatically.
                    </div>
                </div>

                <div class="gstep">
                    <span class="gn gn--amber">2</span>
                    <div>
                        <b>Duplicates get cleaned up.</b> The same person often signs up more than once. The system merges the obvious matches by itself and asks a human only when it’s unsure.
                    </div>
                </div>

                <div class="gstep">
                    <span class="gn gn--blue-dark">3</span>
                    <div>
                        <b>Everyone becomes one clean record.</b> Each person now has a single profile with their full history — and those people also group together under their organization.
                    </div>
                </div>

                <div class="gstep">
                    <span class="gn gn--ink">4</span>
                    <div>
                        <b>Your team acts on it.</b> Build lists (Segments), check the numbers (Reports), and work deals — all reading from that same clean data.
                    </div>
                </div>
            </div>

            <div class="scenario">
                <i class="ti ti-bulb"></i>

                <div>
                    <b>In one sentence:</b> messy sign-ups go in on the left, get cleaned into one record per person in the middle, and come out on the right as lists, reports, and deals your team can actually use. <b>Audit log</b> and <b>Settings</b> quietly keep the whole thing honest and governed.
                </div>
            </div>
        </section>

        <div class="gsec">
            <i class="gsec__icon ti ti-database"></i>
             Data — the records themselves
            </div>

        <section class="crm-data-grid">
            <article class="crm-data-card">
                <span class="crm-data-card__icon crm-data-card__icon--blue">
                    <i class="ti ti-users"></i>
                </span>

                <div>
                    <h3>Contacts</h3>

                    <p>
                        The heart of the system: one clean record per person, no matter how many forms they filled or accounts they made. Each record carries who they are, where they came from, what they’ve learned, and who owns them.
                    </p>

                    <div class="crm-data-card__note">
                        <b>In real life:</b> Priya filled a 2025 Google Form with her Gmail and later made a portal account with her institute email. Here she is <b>one person</b>, with both histories on a single timeline.
                    </div>

                    <a
                        class="crm-data-card__link"
                        href="{{ route('admin.contacts.persons.index') }}"
                    >
                        Open Contacts →
                    </a>
                </div>
            </article>

            <article class="crm-data-card">
                <span class="crm-data-card__icon crm-data-card__icon--teal">
                    <i class="ti ti-building"></i>
                </span>

                <div>
                    <h3>Organizations</h3>

                    <p>
                        The same people rolled up by their institution. Shows how many learners an org has, how many are engaged, who’s a customer, and the licence status — the evidence pack for institutional deals.
                    </p>

                    <div class="crm-data-card__note">
                        <b>In real life:</b> AIIMS has 80 active learners but no licence yet. The org page proves that the obvious pitch is institutional licence proposal, with 80 exact names to reference.
                    </div>

                    <a
                        class="crm-data-card__link"
                        href="{{ route('admin.contacts.organizations.index') }}"
                    >
                        Open Organizations →
                    </a>
                </div>
            </article>

            <article class="crm-data-card">
                <span class="crm-data-card__icon crm-data-card__icon--purple">
                    <i class="ti ti-flag"></i>
                </span>

                <div>
                    <h3>Campaigns &amp; programs</h3>

                    <p>
                        Every workshop/program under one canonical name, with the messy form-title variants mapped underneath — so reports count the same thing every time.
                    </p>

                    <div class="crm-data-card__note">
                        <b>In real life:</b> 161 people registered under 11 messy Preparation Techniques titles. Mapped to <b>From Sample to Sequence (NGS)</b>, all 161 now count toward that program instead of vanishing.
                    </div>

                    <a
                        class="crm-data-card__link"
                        href="{{ route('admin.leads.index') }}"
                    >
                        Open Campaigns &amp; programs →
                    </a>
                </div>
            </article>

            <article class="crm-data-card">
                <span class="crm-data-card__icon crm-data-card__icon--amber">
                    <i class="ti ti-git-merge"></i>
                </span>

                <div>
                    <h3>Merge review</h3>

                    <p>
                        The human checkpoint for duplicates the system isn’t sure about — same name + phone but different emails, or two portal logins. You decide; every action is logged and reversible.
                    </p>

                    <div class="crm-data-card__note">
                        <b>In real life:</b> Exact-email matches merge silently. The gray-area pairs wait here so a wrong guess never corrupts a real record.
                    </div>

                    <a
                        class="crm-data-card__link"
                        href="{{ route('admin.leads.index') }}"
                    >
                        Open Merge review →
                    </a>
                </div>
            </article>
        </section>

        <div class="crm-card-section-title">
            <i class="ti ti-briefcase"></i>
            Work — what your team does with the data
        </div>

        <section class="crm-data-grid">
            <article class="crm-data-card">
                <span class="crm-data-card__icon crm-data-card__icon--blue">
                    <i class="ti ti-bookmark"></i>
                </span>

                <div>
                    <h3>Segments</h3>

                    <p>
                        Saved, named filters that stay live. Build an audience once (“engaged but not yet a customer”) and it keeps refreshing itself — any report or export can read it.
                    </p>

                    <div class="crm-data-card__note">
                        <b>In real life:</b> “NGS Team — cold leads” rebuilds every night, so Monday’s outreach list already includes the weekend’s sign-ups. No re-filtering.
                    </div>

                    <a
                        class="crm-data-card__link"
                        href="{{ route('admin.leads.index') }}"
                    >
                        Open Segments →
                    </a>
                </div>
            </article>

            <article class="crm-data-card">
                <span class="crm-data-card__icon crm-data-card__icon--purple">
                    <i class="ti ti-chart-bar"></i>
                </span>

                <div>
                    <h3>Reports</h3>

                    <p>
                        The monthly-review numbers straight from the deduplicated data: where leads came from, how they move through the funnel, top programs and countries, and won/lost momentum.
                    </p>

                    <div class="crm-data-card__note">
                        <b>In real life:</b> One screen replaces three spreadsheets: the marketing review — and every figure traces back to the same clean records.
                    </div>

                    <a
                        class="crm-data-card__link"
                        href="{{ route('admin.dashboard.index') }}"
                    >
                        Open Reports →
                    </a>
                </div>
            </article>

            <article class="crm-data-card">
                <span class="crm-data-card__icon crm-data-card__icon--green">
                    <i class="ti ti-database-import"></i>
                </span>

                <div>
                    <h3>Imports &amp; sync</h3>

                    <p>
                        The log of every data run from every source — how many rows came in, how many were new, merged, or parked for review — plus drag-and-drop CSV for one-off backfills.
                    </p>

                    <div class="crm-data-card__note">
                        <b>In real life:</b> The 35,112-row Zoho export was dropped in once; new workshop forms now sync hourly on top of that clean base, and a failed sync gets flagged instead of going silent.
                    </div>

                    <a
                        class="crm-data-card__link"
                        href="{{ route('admin.leads.index') }}"
                    >
                        Open Imports &amp; sync →
                    </a>
                </div>
            </article>
        </section>

        <div class="crm-card-section-title">
            <i class="ti ti-settings"></i>
            System — plumbing &amp; governance
        </div>

        <section class="crm-data-grid">
            <article class="crm-data-card">
                <span class="crm-data-card__icon crm-data-card__icon--blue">
                    <i class="ti ti-plug-connected"></i>
                </span>

                <div>
                    <h3>Connectors</h3>

                    <p>
                        The source setup area for Google Forms, portal activity, CSV files, and legacy imports. Each connector defines field mapping, owner rules, and what should happen when data changes.
                    </p>

                    <div class="crm-data-card__note">
                        <b>In real life:</b> A new form can feed the CRM without creating a new spreadsheet habit. Map the fields once, then let the connector keep the records current.
                    </div>

                    <a
                        class="crm-data-card__link"
                        href="{{ route('admin.configuration.index') }}"
                    >
                        Open Connectors →
                    </a>
                </div>
            </article>

            <article class="crm-data-card">
                <span class="crm-data-card__icon crm-data-card__icon--grey">
                    <i class="ti ti-history"></i>
                </span>

                <div>
                    <h3>Audit log</h3>

                    <p>
                        A readable trail of important changes: imports, merges, owner changes, lifecycle updates, and settings changes. It shows who changed what, when, and why.
                    </p>

                    <div class="crm-data-card__note">
                        <b>In real life:</b> If a contact was merged or moved into a segment by mistake, the audit trail explains the action and gives the team confidence to fix it.
                    </div>

                    <a
                        class="crm-data-card__link"
                        href="{{ route('admin.settings.index') }}"
                    >
                        Open Audit log →
                    </a>
                </div>
            </article>
            <article class="crm-data-card">
                <span class="crm-data-card__icon crm-data-card__icon--grey">
                    <i class="ti ti-history"></i>
                </span>

                <div>
                    <h3>Settings</h3>

                    <p>
                        The rules that keep the database clean and governed: dedup thresholds, lead-routing, user roles, and privacy/retention for opt-out, erasure, and backups.
                    </p>

                    <div class="crm-data-card__note">
                        <b>In real life:</b> Settings define when the system can merge automatically, when a human must review, and who can change sensitive records.
                    </div>

                    <a
                        class="crm-data-card__link"
                        href="{{ route('admin.settings.index') }}"
                    >
                        Open Settings →
                    </a>
                </div>
            </article>
        </section>

        <div class="crm-card-section-title">
            <i class="ti ti-arrows-split"></i>
            The two “stage” systems — don’t mix them up
        </div>

        <section class="crm-stage-grid">
            <article class="crm-stage-card">
                <div class="crm-stage-card__title">
                    <b>Lifecycle stage</b>
                    <span>— how engaged a learner is</span>
                </div>

                <div class="crm-pills">
                    <span class="crm-pill crm-pill--grey">Subscriber</span>
                    <span class="crm-pill crm-pill--blue">Lead</span>
                    <span class="crm-pill crm-pill--purple">Engaged</span>
                    <span class="crm-pill crm-pill--green">Customer</span>
                    <span class="crm-pill crm-pill--amber">Dormant</span>
                </div>

                <p>
                    Set <b>automatically</b> by what the learner does. Register to form = <b>Lead</b>; finish a first lesson = <b>Engaged</b>; buy a subscription = <b>Customer</b>; go quiet 60+ days = <b>Dormant</b>. This is the column you see on the Contacts table and what most Segments and Reports filter on.
                </p>
            </article>

            <article class="crm-stage-card">
                <div class="crm-stage-card__title">
                    <b>Sales pipeline</b>
                    <span>— where a deal stands</span>
                </div>

                <div class="crm-pills">
                    <span class="crm-pill crm-pill--grey">New</span>
                    <span class="crm-pill crm-pill--blue">Contacted</span>
                    <span class="crm-pill crm-pill--purple">Qualified</span>
                    <span class="crm-pill crm-pill--amber">Proposal</span>
                    <span class="crm-pill crm-pill--green">Won</span>
                </div>

                <p>
                    Set <b>manually</b> by the assigned owner as they work a deal — mostly for institutional licenses and upsells. It lives inside a contact’s or organization’s sales view, not the main table. A person can be a <b>Customer</b> lifecycle and at <b>Proposal</b> pipeline for a bigger licence at the same time.
                </p>
            </article>
        </section>

        <div class="crm-stage-summary">
            <b>The short version:</b> Lifecycle answers “how warm is this learner?” and updates itself. Pipeline answers “how far along is the deal?” and is driven by a salesperson. They run in parallel on the same record.
        </div>

        <div class="crm-card-section-title">
            <i class="ti ti-gauge"></i>
            How the lead score is calculated
        </div>

        <section class="crm-score-grid">
            <article class="crm-score-card">
                <p>
                    Every contact gets a 0–100 score that updates automatically. It’s a weighted sum of three things — <b>who they are</b> (fit), <b>what they’ve done</b> (engagement), and <b>how recently</b> (recency) — so the highest scores rise to people who are both a good fit and actively learning. It’s a priority signal for outreach, not a grade.
                </p>

                <div class="crm-score-list">
                    <div class="crm-score-row">
                        <span>Completed a lesson</span>
                        <strong>+ up to 40 pts total</strong>
                    </div>

                    <div class="crm-score-row">
                        <span>Made a purchase / subscription</span>
                        <strong>+25 pts</strong>
                    </div>

                    <div class="crm-score-row">
                        <span>Profile fit (PhD / faculty, target program, target country)</span>
                        <strong>+15 pts</strong>
                    </div>

                    <div class="crm-score-row">
                        <span>Registered for a program</span>
                        <strong>+10 pts</strong>
                    </div>

                    <div class="crm-score-row">
                        <span>High activity in last 30 days</span>
                        <strong>+10 pts</strong>
                    </div>
                </div>
            </article>

            <article class="crm-score-card">
                <h3>How to read a score</h3>

                <div class="crm-score-band">
                    <p>
                        <strong class="crm-score-band__hot">75–100</strong>
                        Hot — engaged + good fit. Prioritize.
                    </p>
                    <p>
                        <strong class="crm-score-band__warm">50–74</strong>
                        Warm — some activity; worth nurturing.
                    </p>

                    <p>
                        <strong class="crm-score-band__cold">0–49</strong>
                        Cold — registered but little/no engagement.
                    </p>
                </div>

                <div class="crm-data-card__note">
                    <b>Example — Priya (92):</b> PhD at a target institution (fit) + 14 lessons done + subscription purchase + active this week = a near-max score. A new sign-up with no lessons sits around 20–30.
                </div>
            </article>
        </section>

        <div class="crm-card-section-title">
            <i class="ti ti-briefcase"></i>
            License status (on Organizations) — what each label means
                </div>

               
                <section class="crm-data-grid">
            <article class="crm-data-card">
                <span class="crm-data-card__icon crm-data-card__icon--blue">
                    <i class="ti ti-users"></i>
                </span>

                <div>
                    <h3>Contacts</h3>

                    <p>
                        The heart of the system: one clean record per person, no matter how many forms they filled or accounts they made. Each record carries who they are, where they came from, what they’ve learned, and who owns them.
                    </p>

                    <div class="crm-data-card__note">
                        <b>In real life:</b> Priya filled a 2025 Google Form with her Gmail and later made a portal account with her institute email. Here she is <b>one person</b>, with both histories on a single timeline.
                    </div>

                    <a
                        class="crm-data-card__link"
                        href="{{ route('admin.contacts.persons.index') }}"
                    >
                        Open Contacts →
                    </a>
                </div>
            </article>

            <article class="crm-data-card">
                <span class="crm-data-card__icon crm-data-card__icon--teal">
                    <i class="ti ti-building"></i>
                </span>

                <div>
                    <h3>Organizations</h3>

                    <p>
                        The same people rolled up by their institution. Shows how many learners an org has, how many are engaged, who’s a customer, and the licence status — the evidence pack for institutional deals.
                    </p>

                    <div class="crm-data-card__note">
                        <b>In real life:</b> AIIMS has 80 active learners but no licence yet. The org page proves that the obvious pitch is institutional licence proposal, with 80 exact names to reference.
                    </div>

                    <a
                        class="crm-data-card__link"
                        href="{{ route('admin.contacts.organizations.index') }}"
                    >
                        Open Organizations →
                    </a>
                </div>
            </article>

            <article class="crm-data-card">
                <span class="crm-data-card__icon crm-data-card__icon--purple">
                    <i class="ti ti-flag"></i>
                </span>

                <div>
                    <h3>Campaigns &amp; programs</h3>

                    <p>
                        Every workshop/program under one canonical name, with the messy form-title variants mapped underneath — so reports count the same thing every time.
                    </p>

                    <div class="crm-data-card__note">
                        <b>In real life:</b> 161 people registered under 11 messy Preparation Techniques titles. Mapped to <b>From Sample to Sequence (NGS)</b>, all 161 now count toward that program instead of vanishing.
                    </div>

                    <a
                        class="crm-data-card__link"
                        href="{{ route('admin.leads.index') }}"
                    >
                        Open Campaigns &amp; programs →
                    </a>
                </div>
            </article>

            <article class="crm-data-card">
                <span class="crm-data-card__icon crm-data-card__icon--amber">
                    <i class="ti ti-git-merge"></i>
                </span>

                <div>
                    <h3>Merge review</h3>

                    <p>
                        The human checkpoint for duplicates the system isn’t sure about — same name + phone but different emails, or two portal logins. You decide; every action is logged and reversible.
                    </p>

                    <div class="crm-data-card__note">
                        <b>In real life:</b> Exact-email matches merge silently. The gray-area pairs wait here so a wrong guess never corrupts a real record.
                    </div>

                    <a
                        class="crm-data-card__link"
                        href="{{ route('admin.leads.index') }}"
                    >
                        Open Merge review →
                    </a>
                </div>
            </article>
        </section>

        <section class="mt-5 rounded-2xl bg-[var(--card)] p-5 shadow-sm dark:bg-gray-900">
            <h2 class="text-xl font-bold">
                Frequently used CRM rules
            </h2>

            <div class="mt-4 divide-y divide-[var(--line)] overflow-hidden rounded-2xl border border-[var(--line)] dark:divide-gray-800 dark:border-gray-800">
                <div class="grid grid-cols-[220px_1fr] gap-4 p-4 max-md:grid-cols-1">
                    <p class="font-semibold">When to create a lead?</p>
                    <p class="text-sm leading-6 text-[var(--muted)] dark:text-gray-300">Create a lead whenever a new prospect shows interest, asks for pricing, downloads a resource, calls, or fills a form.</p>
                </div>

                <div class="grid grid-cols-[220px_1fr] gap-4 p-4 max-md:grid-cols-1">
                    <p class="font-semibold">When to create a quote?</p>
                    <p class="text-sm leading-6 text-[var(--muted)] dark:text-gray-300">Create a quote after the need is confirmed and the customer expects a commercial proposal.</p>
                </div>

                <div class="grid grid-cols-[220px_1fr] gap-4 p-4 max-md:grid-cols-1">
                    <p class="font-semibold">When to close a deal?</p>
                    <p class="text-sm leading-6 text-[var(--muted)] dark:text-gray-300">Mark it won after customer confirmation. Mark it lost only with a clear reason so reports stay useful.</p>
                </div>
            </div>
        </section>
    </div>

    {!! view_render_event('admin.dashboard.index.content.after') !!}
</x-admin::layouts>
