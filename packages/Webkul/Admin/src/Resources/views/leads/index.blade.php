<x-admin::layouts>
    <x-slot:title>
        Campaigns &amp; programs
    </x-slot>

    {!! view_render_event('admin.leads.index.content.before') !!}

    @php
        $metrics = [
            ['icon' => 'ti ti-folder', 'label' => 'CANONICAL CAMPAIGNS', 'value' => '9', 'note' => '9 categories'],
            ['icon' => 'ti ti-link', 'label' => 'ALIASES MAPPED', 'value' => '188', 'note' => 'auto-resolved on import'],
            ['icon' => 'ti ti-alert-circle', 'label' => 'NEEDS NAMING', 'value' => '7', 'note' => 'unmapped variants', 'review' => true],
            ['icon' => 'ti ti-users', 'label' => 'LEADS ATTRIBUTED', 'value' => '31,402', 'note' => '90% of all leads'],
        ];

        $campaigns = [
            ['name' => 'RNA-Seq Data Analysis', 'category' => 'Transcriptomics', 'aliases' => '6', 'leads' => '233', 'customers' => '31', 'conversion' => '13%', 'conversion_class' => 'campaigns-score--good', 'status' => ['label' => 'mapped', 'class' => 'campaigns-status--mapped']],
            ['name' => 'From Sample to Sequencer (NGS)', 'category' => 'NGS Wet Lab', 'aliases' => '4', 'leads' => '161', 'customers' => '12', 'conversion' => '7%', 'conversion_class' => 'campaigns-score--neutral', 'status' => ['label' => 'mapped', 'class' => 'campaigns-status--mapped']],
            ['name' => 'AI in Drug Discovery', 'category' => 'AI / Cheminformatics', 'aliases' => '5', 'leads' => '142', 'customers' => '11', 'conversion' => '8%', 'conversion_class' => 'campaigns-score--warn', 'status' => ['label' => 'mapped', 'class' => 'campaigns-status--mapped']],
            ['name' => 'Clinical Genomics & Mutation Analysis', 'category' => 'Clinical', 'aliases' => '4', 'leads' => '120', 'customers' => '14', 'conversion' => '12%', 'conversion_class' => 'campaigns-score--good', 'status' => ['label' => 'mapped', 'class' => 'campaigns-status--mapped']],
            ['name' => 'Single-Cell RNA-Seq', 'category' => 'Transcriptomics', 'aliases' => '3', 'leads' => '88', 'customers' => '9', 'conversion' => '10%', 'conversion_class' => 'campaigns-score--warn', 'status' => ['label' => 'mapped', 'class' => 'campaigns-status--mapped']],
            ['name' => 'Metagenomics & Microbiome', 'category' => 'Metagenomics', 'aliases' => '3', 'leads' => '76', 'customers' => '5', 'conversion' => '7%', 'conversion_class' => 'campaigns-score--neutral', 'status' => ['label' => 'review', 'class' => 'campaigns-status--review']],
            ['name' => 'Machine Learning for Omics (Python)', 'category' => 'ML', 'aliases' => '3', 'leads' => '64', 'customers' => '8', 'conversion' => '13%', 'conversion_class' => 'campaigns-score--good', 'status' => ['label' => 'mapped', 'class' => 'campaigns-status--mapped']],
            ['name' => 'Cheminformatics / Molecular Modeling', 'category' => 'Cheminformatics', 'aliases' => '2', 'leads' => '47', 'customers' => '4', 'conversion' => '9%', 'conversion_class' => 'campaigns-score--warn', 'status' => ['label' => 'mapped', 'class' => 'campaigns-status--mapped']],
            ['name' => 'Precision Oncology Essentials', 'category' => 'Oncology', 'aliases' => '2', 'leads' => '39', 'customers' => '3', 'conversion' => '8%', 'conversion_class' => 'campaigns-score--warn', 'status' => ['label' => 'mapped', 'class' => 'campaigns-status--mapped']],
        ];

        $aliases = [
            ['name' => 'RNA-Seq Data Analysis: From Raw Reads to Biological Insight', 'confidence' => 'conf 0.98', 'status' => 'auto-mapped', 'class' => 'campaigns-alias__confidence--good'],
            ['name' => 'RNA-Seq Data Analysis using R Programming', 'confidence' => 'conf 0.86', 'status' => 'auto-mapped', 'class' => 'campaigns-alias__confidence--good'],
            ['name' => 'Bulk RNA-Seq Data Analysis', 'confidence' => 'conf 0.94', 'status' => 'auto-mapped', 'class' => 'campaigns-alias__confidence--good'],
            ['name' => 'RNASeq workshop 2024', 'confidence' => 'conf 0.88', 'status' => 'Approve', 'class' => 'campaigns-alias__confidence--neutral'],
            ['name' => 'RNA Seq Pipeline (Differentialization)', 'confidence' => 'conf 0.71', 'status' => 'Approve', 'class' => 'campaigns-alias__confidence--warn'],
        ];
    @endphp

    <div class="campaigns-page">
        <section class="campaigns-hero">
            <h1>Campaigns &amp; programs</h1>
            <p>Canonical program names with their messy aliases mapped underneath — so every report counts the same thing. Tick rows to manually group workshops under one canonical name.</p>
        </section>

        <section class="campaigns-metrics">
            @foreach ($metrics as $metric)
                <article class="campaigns-metric {{ ! empty($metric['review']) ? 'campaigns-metric--review' : '' }}">
                    <div class="campaigns-metric__label">
                        <i class="{{ $metric['icon'] }}"></i>
                        {{ $metric['label'] }}
                    </div>

                    <div class="campaigns-metric__value">{{ $metric['value'] }}</div>
                    <div class="campaigns-metric__note">{{ $metric['note'] }}</div>
                </article>
            @endforeach
        </section>

        <section class="campaigns-toolbar">
            <div class="campaigns-search">
                <i class="icon-search"></i>
                <input
                    type="text"
                    placeholder="Search campaigns or categories..."
                >
            </div>

            <a
                href="{{ route('admin.leads.create') }}"
                class="campaigns-action campaigns-action--new"
            >
                <i class="ti ti-plus"></i>
                New campaign
            </a>

            <button
                type="button"
                class="campaigns-action"
            >
                <i class="ti ti-download"></i>
                Export
            </button>
        </section>

        <section class="campaigns-table-shell">
            <div class="campaigns-table-count">
                Showing <strong>9</strong> of <strong>9</strong> campaigns
            </div>

            <div class="campaigns-table">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="campaigns-checkbox"></th>
                            <th>Canonical Campaign</th>
                            <th>Category</th>
                            <th>Aliases</th>
                            <th>Leads</th>
                            <th>Customers</th>
                            <th>Conversion</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($campaigns as $campaign)
                            <tr>
                                <td><input type="checkbox" class="campaigns-checkbox"></td>
                                <td class="campaigns-name">{{ $campaign['name'] }}</td>
                                <td>{{ $campaign['category'] }}</td>
                                <td><span class="campaigns-alias-count"><i class="ti ti-link"></i> {{ $campaign['aliases'] }}</span></td>
                                <td class="campaigns-number">{{ $campaign['leads'] }}</td>
                                <td>{{ $campaign['customers'] }}</td>
                                <td><span class="{{ $campaign['conversion_class'] }}">{{ $campaign['conversion'] }}</span></td>
                                <td>
                                    <span class="campaigns-status {{ $campaign['status']['class'] }}">
                                        <i class="ti ti-check"></i>
                                        {{ $campaign['status']['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <!-- <section class="campaigns-panel">
            <div class="campaigns-panel__header">
                <h2>Alias mapping — “RNA-Seq Data Analysis”</h2>
                <span>6 variants · 1 canonical name</span>
            </div>

            <p class="campaigns-panel__copy">
                These raw names arrived from different Google Forms and the Zoho export. The system maps each to one canonical campaign so leads, customers and revenue all roll up correctly.
            </p>

            <div class="campaigns-aliases">
                @foreach ($aliases as $alias)
                    <div class="campaigns-alias">
                        <span>{{ $alias['name'] }}</span>

                        <div>
                            <span class="campaigns-alias__confidence {{ $alias['class'] }}">{{ $alias['confidence'] }}</span>
                            <span class="campaigns-alias__status">{{ $alias['status'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="campaigns-panel__note">
                Send these mappings from your existing “Leads Campaign Cleanup Review” workbook — the canonical names and variants are already there.
            </div>
        </section> -->

        <!-- <section class="campaigns-unmapped">
            <div class="campaigns-unmapped__header">
                <h2>Unmapped — needs naming</h2>
                <span>1 of 7</span>
            </div>

            <div class="campaigns-unmapped__body">
                <div>
                    <strong>“Library Preparation Techniques for NGS”</strong>
                    <p>161 registrations · Google Form · January</p>
                </div>

                <select>
                    <option>Map to canonical...</option>
                </select>

                <button type="button">
                    <i class="ti ti-check"></i>
                    Apply
                </button>
            </div>
        </section> -->
    </div>

    {!! view_render_event('admin.leads.index.content.after') !!}
</x-admin::layouts>
