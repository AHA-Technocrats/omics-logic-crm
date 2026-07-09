<x-admin::layouts>
    <x-slot:title>
        {{ $segment->name }}
    </x-slot>

    <v-segment-view initial-tab="{{ request('tab', 'persons') }}"></v-segment-view>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-segment-view-template"
        >
            <div class="flex flex-col gap-4">
                <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] rounded-lg border border-gray-300 bg-white px-4 py-4 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex flex-col gap-2">
                            <x-admin::breadcrumbs name="omics.segments.view" :entity="$segment" />

                            <div class="text-xl font-bold dark:text-white">
                                {{ $segment->name }}
                            </div>

                            @if ($segment->description)
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ $segment->description }}
                                </p>
                            @endif

                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @if ($segment->last_refreshed_at)
                                    @lang('omicslogic::app.segments.view-subtitle', [
                                        'count' => number_format($personCount),
                                        'date' => $segment->last_refreshed_at->format('M j, Y g:i A'),
                                    ])
                                @else
                                    @lang('omicslogic::app.segments.view-subtitle-never', [
                                        'count' => number_format($personCount),
                                    ])
                                @endif
                            </p>

                            <div class="flex flex-col gap-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    @lang('omicslogic::app.segments.active-filters')
                                </p>

                                @if (count($filterLabels))
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($filterLabels as $filter)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                                <span class="text-gray-500 dark:text-gray-400">{{ $filter['label'] }}:</span>
                                                {{ $filter['value'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        @lang('omicslogic::app.segments.no-filters')
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2.5">
                            @if (bouncer()->hasPermission('segments.view'))
                                <form
                                    method="POST"
                                    action="{{ route('admin.omics.segments.view.refresh', $segment->id) }}"
                                >
                                    @csrf
                                    <button type="submit" class="secondary-button">
                                        @lang('omicslogic::app.segments.refresh-count')
                                    </button>
                                </form>
                            @endif

                            @if (bouncer()->hasPermission('segments.edit'))
                                <a
                                    href="{{ route('admin.omics.segments.edit', $segment->id) }}"
                                    class="primary-button"
                                >
                                    @lang('admin::app.acl.edit')
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="inline-flex w-fit gap-1 rounded-xl border border-gray-200 bg-gray-100 p-1 dark:border-gray-800 dark:bg-gray-950">
                    <button
                        type="button"
                        @click="switchTab('persons')"
                        class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all"
                        :class="tab === 'persons' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    >
                        <span class="icon-contact text-lg"></span>
                        @lang('omicslogic::app.segments.tabs.persons')
                        <span
                            class="rounded-full px-2 py-0.5 text-xs font-bold"
                            :class="tab === 'persons' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'bg-gray-200 text-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                        >{{ number_format($personCount) }}</span>
                    </button>

                    <button
                        type="button"
                        @click="switchTab('leads')"
                        class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all"
                        :class="tab === 'leads' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    >
                        <span class="icon-leads text-lg"></span>
                        @lang('omicslogic::app.segments.tabs.leads')
                        <span
                            class="rounded-full px-2 py-0.5 text-xs font-bold"
                            :class="tab === 'leads' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'bg-gray-200 text-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                        >{{ number_format($leadCount) }}</span>
                    </button>
                </div>

                <div v-show="tab === 'persons'">
                    <x-admin::datagrid :src="route('admin.omics.segments.view.persons', $segment->id)">
                        <x-admin::shimmer.datagrid />
                    </x-admin::datagrid>
                </div>

                <div v-show="tab === 'leads'">
                    <x-admin::datagrid :src="route('admin.omics.segments.view.leads', $segment->id)">
                        <x-admin::shimmer.datagrid />
                    </x-admin::datagrid>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-segment-view', {
                template: '#v-segment-view-template',

                props: {
                    initialTab: {
                        type: String,
                        default: 'persons',
                    },
                },

                data() {
                    return {
                        tab: this.initialTab === 'leads' ? 'leads' : 'persons',
                    };
                },

                methods: {
                    switchTab(tab) {
                        this.tab = tab;

                        const url = new URL(window.location.href);
                        url.searchParams.set('tab', tab);
                        window.history.replaceState({}, '', url);
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
