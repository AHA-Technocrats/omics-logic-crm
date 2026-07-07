<x-admin::layouts>
    <x-slot:title>
        @lang('omicslogic::app.merge.title')
    </x-slot>

    <v-merge-review initial-tab="{{ $activeTab }}"></v-merge-review>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-merge-review-template"
        >
            <div class="flex flex-col gap-4">
                {{-- Header --}}
                <div class="flex flex-col gap-1 rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <x-admin::breadcrumbs name="omics.merge" />

                    <div class="text-xl font-bold dark:text-white">
                        @lang('omicslogic::app.merge.title')
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        <span v-show="tab === 'persons'">@lang('omicslogic::app.merge.subtitle', ['count' => $pendingCount])</span>
                        <span v-show="tab === 'organizations'">@lang('omicslogic::app.merge-organizations.subtitle', ['count' => $organizationPendingCount])</span>
                    </p>
                </div>

                {{-- Segmented tab control --}}
                <div class="inline-flex w-fit gap-1 rounded-xl border border-gray-200 bg-gray-100 p-1 dark:border-gray-800 dark:bg-gray-950">
                    <button
                        type="button"
                        @click="switchTab('persons')"
                        class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all"
                        :class="tab === 'persons' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    >
                        <span class="icon-contact text-lg"></span>
                        @lang('omicslogic::app.merge.tabs.persons')
                        <span
                            class="rounded-full px-2 py-0.5 text-xs font-bold"
                            :class="tab === 'persons' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'bg-gray-200 text-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                        >{{ number_format($pendingCount) }}</span>
                    </button>

                    <button
                        type="button"
                        @click="switchTab('organizations')"
                        class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all"
                        :class="tab === 'organizations' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    >
                        <span class="icon-organization text-lg"></span>
                        @lang('omicslogic::app.merge.tabs.organizations')
                        <span
                            class="rounded-full px-2 py-0.5 text-xs font-bold"
                            :class="tab === 'organizations' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'bg-gray-200 text-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                        >{{ number_format($organizationPendingCount) }}</span>
                    </button>
                </div>

                {{-- ============================ PERSONS ============================ --}}
                <div v-show="tab === 'persons'" class="flex flex-col gap-4">
                    <div class="flex flex-wrap gap-4">
                        <div class="min-w-[200px] flex-1 rounded-xl border border-l-4 border-gray-200 border-l-blue-500 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge.stats-pending')</p>
                            <p class="mt-1 text-2xl font-bold dark:text-white">{{ number_format($stats['pending']) }}</p>
                        </div>

                        <div class="min-w-[200px] flex-1 rounded-xl border border-l-4 border-gray-200 border-l-green-500 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge.stats-auto-merged')</p>
                            <p class="mt-1 text-2xl font-bold dark:text-white">{{ number_format($stats['auto_merged_7d']) }}</p>
                        </div>

                        <div class="min-w-[200px] flex-1 rounded-xl border border-l-4 border-gray-200 border-l-gray-400 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge.stats-separate')</p>
                            <p class="mt-1 text-2xl font-bold dark:text-white">{{ number_format($stats['kept_separate_7d']) }}</p>
                        </div>

                        <div class="min-w-[200px] flex-1 rounded-xl border border-l-4 border-gray-200 border-l-purple-500 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge.stats-avg-confidence')</p>
                            <p class="mt-1 text-2xl font-bold dark:text-white">{{ number_format($stats['avg_confidence'] * 100, 0) }}%</p>
                        </div>
                    </div>

                    @if ($pairs->isEmpty())
                        <div class="flex min-h-[280px] flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center dark:border-gray-800 dark:bg-gray-900">
                            <span class="icon-contact mb-3 text-5xl text-gray-300 dark:text-gray-600"></span>
                            <p class="text-base font-medium text-gray-700 dark:text-gray-200">@lang('omicslogic::app.merge.empty')</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge.empty-hint')</p>
                        </div>
                    @else
                        @foreach ($pairs as $card)
                            @php
                                $pair = $card['pair'];
                                $personA = $card['person_a'];
                                $personB = $card['person_b'];
                                $signals = $pair->match_signals ?? [];
                                $confidence = (float) $pair->confidence;
                                $confidenceBadge = $confidence >= 0.9
                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100'
                                    : ($confidence >= 0.7
                                        ? 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-100'
                                        : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300');
                            @endphp

                            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                                    <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $confidenceBadge }}">
                                        @lang('omicslogic::app.merge.confidence', ['value' => number_format($confidence * 100, 0)])
                                    </span>
                                    <span class="text-xs text-gray-400">#{{ $pair->id }}</span>
                                </div>

                                @if (! empty($signals))
                                    <div class="mb-4 flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge.signals'):</span>
                                        @foreach ($signals as $signal)
                                            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $signal }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mb-4 grid gap-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                                    @foreach ([$personA, $personB] as $person)
                                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/40">
                                            <div class="mb-3 flex items-center gap-3">
                                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-bold uppercase text-blue-700 dark:bg-blue-900 dark:text-blue-200">
                                                    {{ mb_substr($person['name'] ?? '?', 0, 1) }}
                                                </span>
                                                <a href="{{ route('admin.contacts.persons.view', $person['id']) }}" class="font-semibold text-blue-600 hover:underline dark:text-blue-400">
                                                    {{ $person['name'] }}
                                                </a>
                                            </div>
                                            <dl class="space-y-1.5 text-sm text-gray-600 dark:text-gray-300">
                                                <div class="flex justify-between gap-2"><dt class="text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge.person-fields.email')</dt><dd class="text-right dark:text-white">{{ $person['email'] ?: '—' }}</dd></div>
                                                <div class="flex justify-between gap-2"><dt class="text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge.person-fields.phone')</dt><dd class="text-right dark:text-white">{{ $person['phone'] }}</dd></div>
                                                <div class="flex justify-between gap-2"><dt class="text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge.person-fields.organization')</dt><dd class="text-right dark:text-white">{{ $person['organization'] }}</dd></div>
                                                <div class="flex justify-between gap-2"><dt class="text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge.person-fields.program')</dt><dd class="text-right dark:text-white">{{ $person['program'] }}</dd></div>
                                                <div class="flex justify-between gap-2"><dt class="text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge.person-fields.source')</dt><dd class="text-right dark:text-white">{{ $person['source'] }}</dd></div>
                                                <div class="flex justify-between gap-2"><dt class="text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge.person-fields.added')</dt><dd class="text-right dark:text-white">{{ $person['added'] }}</dd></div>
                                            </dl>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('admin.omics.merge.resolve', $pair->id) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="merge">
                                        <button type="submit" class="primary-button">@lang('omicslogic::app.merge.merge-btn')</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.omics.merge.resolve', $pair->id) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="separate">
                                        <button type="submit" class="secondary-button">@lang('omicslogic::app.merge.separate-btn')</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.omics.merge.resolve', $pair->id) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="dismiss">
                                        <button type="submit" class="transparent-button">@lang('omicslogic::app.merge.dismiss-btn')</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- ========================= ORGANIZATIONS ========================= --}}
                <div v-show="tab === 'organizations'" class="flex flex-col gap-4">
                    <div class="flex flex-wrap gap-4">
                        <div class="min-w-[200px] flex-1 rounded-xl border border-l-4 border-gray-200 border-l-blue-500 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge-organizations.stats-pending')</p>
                            <p class="mt-1 text-2xl font-bold dark:text-white">{{ number_format($organizationStats['pending']) }}</p>
                        </div>

                        <div class="min-w-[200px] flex-1 rounded-xl border border-l-4 border-gray-200 border-l-green-500 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge-organizations.stats-merged')</p>
                            <p class="mt-1 text-2xl font-bold dark:text-white">{{ number_format($organizationStats['merged_7d']) }}</p>
                        </div>

                        <div class="min-w-[200px] flex-1 rounded-xl border border-l-4 border-gray-200 border-l-gray-400 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge-organizations.stats-separate')</p>
                            <p class="mt-1 text-2xl font-bold dark:text-white">{{ number_format($organizationStats['kept_separate_7d']) }}</p>
                        </div>

                        <div class="min-w-[200px] flex-1 rounded-xl border border-l-4 border-gray-200 border-l-purple-500 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge-organizations.stats-avg-confidence')</p>
                            <p class="mt-1 text-2xl font-bold dark:text-white">{{ number_format($organizationStats['avg_confidence'] * 100, 0) }}%</p>
                        </div>
                    </div>

                    @if ($organizationPairs->isEmpty())
                        <div class="flex min-h-[280px] flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center dark:border-gray-800 dark:bg-gray-900">
                            <span class="icon-organization mb-3 text-5xl text-gray-300 dark:text-gray-600"></span>
                            <p class="text-base font-medium text-gray-700 dark:text-gray-200">@lang('omicslogic::app.merge-organizations.empty')</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge-organizations.empty-hint')</p>
                        </div>
                    @else
                        @foreach ($organizationPairs as $card)
                            @php
                                $pair = $card['pair'];
                                $organizationA = $card['organization_a'];
                                $organizationB = $card['organization_b'];
                                $signals = $pair->match_signals ?? [];
                                $confidence = (float) $pair->confidence;
                                $confidenceBadge = $confidence >= 0.9
                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100'
                                    : ($confidence >= 0.7
                                        ? 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-100'
                                        : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300');
                            @endphp

                            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                                    <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $confidenceBadge }}">
                                        @lang('omicslogic::app.merge-organizations.confidence', ['value' => number_format($confidence * 100, 0)])
                                    </span>
                                    <span class="text-xs text-gray-400">#{{ $pair->id }}</span>
                                </div>

                                @if (! empty($signals))
                                    <div class="mb-4 flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge-organizations.signals'):</span>
                                        @foreach ($signals as $signal)
                                            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $signal }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mb-4 grid gap-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                                    @foreach ([$organizationA, $organizationB] as $organization)
                                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/40">
                                            <div class="mb-3 flex items-center gap-3">
                                                <span class="icon-organization flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-lg text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200"></span>
                                                <a href="{{ route('admin.contacts.organizations.view', $organization['id']) }}" class="font-semibold text-blue-600 hover:underline dark:text-blue-400">
                                                    {{ $organization['name'] }}
                                                </a>
                                            </div>
                                            <dl class="space-y-1.5 text-sm text-gray-600 dark:text-gray-300">
                                                <div class="flex justify-between gap-2"><dt class="text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge-organizations.org-fields.country')</dt><dd class="text-right dark:text-white">{{ $organization['country'] }}</dd></div>
                                                <div class="flex justify-between gap-2"><dt class="text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge-organizations.org-fields.type')</dt><dd class="text-right dark:text-white">{{ $organization['type'] }}</dd></div>
                                                <div class="flex justify-between gap-2"><dt class="text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge-organizations.org-fields.contacts')</dt><dd class="text-right dark:text-white">{{ $organization['contacts'] }}</dd></div>
                                                <div class="flex justify-between gap-2"><dt class="text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge-organizations.org-fields.owner')</dt><dd class="text-right dark:text-white">{{ $organization['owner'] }}</dd></div>
                                                <div class="flex justify-between gap-2"><dt class="text-gray-500 dark:text-gray-400">@lang('omicslogic::app.merge-organizations.org-fields.added')</dt><dd class="text-right dark:text-white">{{ $organization['added'] }}</dd></div>
                                            </dl>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('admin.omics.merge_organizations.resolve', $pair->id) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="merge">
                                        <button type="submit" class="primary-button">@lang('omicslogic::app.merge-organizations.merge-btn')</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.omics.merge_organizations.resolve', $pair->id) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="separate">
                                        <button type="submit" class="secondary-button">@lang('omicslogic::app.merge-organizations.separate-btn')</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.omics.merge_organizations.resolve', $pair->id) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="dismiss">
                                        <button type="submit" class="transparent-button">@lang('omicslogic::app.merge-organizations.dismiss-btn')</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-merge-review', {
                template: '#v-merge-review-template',

                props: ['initialTab'],

                data() {
                    let tab = this.initialTab || 'persons';

                    try {
                        const urlTab = new URLSearchParams(window.location.search).get('tab');
                        const saved = localStorage.getItem('omics_merge_tab');

                        if (urlTab === 'persons' || urlTab === 'organizations') {
                            tab = urlTab;
                        } else if (saved === 'persons' || saved === 'organizations') {
                            tab = saved;
                        }
                    } catch (error) {
                        // localStorage unavailable — fall back to the default tab.
                    }

                    return { tab };
                },

                mounted() {
                    this.persist(this.tab);
                },

                methods: {
                    switchTab(tab) {
                        this.tab = tab;
                        this.persist(tab);
                    },

                    persist(tab) {
                        try {
                            localStorage.setItem('omics_merge_tab', tab);
                        } catch (error) {
                            // Ignore storage failures (private mode, quota, etc.).
                        }
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
