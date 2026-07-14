<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.leads.index.title')
    </x-slot>

    <!-- Header -->
    {!! view_render_event('admin.leads.index.header.before') !!}

    <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
        {!! view_render_event('admin.leads.index.header.left.before') !!}

        <div class="flex flex-col gap-2">
            <!-- Breadcrumb's -->
            <x-admin::breadcrumbs name="leads" />

            <div class="text-xl font-bold dark:text-white">
                @lang('admin::app.leads.index.title')
            </div>
        </div>

        {!! view_render_event('admin.leads.index.header.left.after') !!}

        {!! view_render_event('admin.leads.index.header.right.before') !!}

        <div class="flex items-center gap-x-2.5">
            <!-- Upload File for Lead Creation -->
            @if(core()->getConfigData('general.magic_ai.doc_generation.enabled'))
                @include('admin::leads.index.upload')
            @endif

            @if ((request()->view_type ?? "kanban") == "table")
                <!-- Export Modal -->
                <x-admin::datagrid.export :src="route('admin.leads.index')" />
            @endif

            <!-- Create button for Leads -->
            <div class="flex items-center gap-x-2.5">
                @if (bouncer()->hasPermission('organizations.edit'))
                    <a
                        href="{{ route('admin.mass_assign.index') }}"
                        class="secondary-button"
                    >
                        @lang('admin::app.mass_assign.btn')
                    </a>
                @endif

                @if (bouncer()->hasPermission('leads.create'))
                    <a
                        href="{{ route('admin.leads.create', request()->query()) }}"
                        class="primary-button"
                    >
                        @lang('admin::app.leads.index.create-btn')
                    </a>
                @endif
            </div>
        </div>

        {!! view_render_event('admin.leads.index.header.right.after') !!}
    </div>

    {!! view_render_event('admin.leads.index.header.after') !!}

    {!! view_render_event('admin.leads.index.content.before') !!}

    <!-- Content -->
    <div class="[&>*>*>*.toolbarRight]:max-lg:w-full [&>*>*>*.toolbarRight]:max-lg:justify-between [&>*>*>*.toolbarRight]:max-md:gap-y-2 [&>*>*>*.toolbarRight]:max-md:flex-wrap mt-3.5 [&>*>*:nth-child(1)]:max-lg:!flex-wrap">
        @if ((request()->view_type ?? "kanban") == "table")
            @include('admin::leads.index.table')
        @else
            @include('admin::leads.index.kanban')
        @endif
    </div>

    {!! view_render_event('admin.leads.index.content.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-leads-filters-template"
        >
            <div class="flex items-center gap-2 relative">
                <x-admin::dropdown>
                    <x-slot:toggle>
                        <button type="button" class="inline-flex min-w-[160px] cursor-pointer appearance-none items-center justify-between gap-x-2 rounded-md border bg-white px-2.5 py-1.5 text-center leading-6 text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400">
                            <span class="flex items-center gap-2 whitespace-nowrap">
                                <i class="icon-calendar text-lg"></i>
                                <span>@{{ currentLabel }}</span>
                            </span>
                            <span class="icon-down-arrow text-xl"></span>
                        </button>
                    </x-slot>
                    
                    <x-slot:menu class="!p-0 shadow-[0_5px_20px_rgba(0,0,0,0.15)] dark:border-gray-800">
                        <x-admin::dropdown.menu.item v-for="(label, value) in presets" ::key="value" ::class="{'bg-gray-100 dark:bg-gray-800 font-semibold': filters.date_range === value}" @click="selectPreset(value)">
                            @{{ label }}
                        </x-admin::dropdown.menu.item>
                        <div class="border-t border-gray-200 dark:border-gray-800"></div>
                        <x-admin::dropdown.menu.item ::class="{'bg-gray-100 dark:bg-gray-800 font-semibold': filters.date_range === 'date_wise'}" @click="selectPreset('date_wise')">
                            Custom Range
                        </x-admin::dropdown.menu.item>
                    </x-slot>
                </x-admin::dropdown>
                
                <div class="items-center gap-2" :style="{ display: filters.date_range === 'date_wise' ? 'flex' : 'none' }">
                    <div class="w-[140px]">
                        <x-admin::flat-picker.date ::allow-input="false">
                            <input type="text" v-model="filters.date_from" placeholder="From" class="w-full rounded-md border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                        </x-admin::flat-picker.date>
                    </div>
                    <span class="text-gray-400">-</span>
                    <div class="w-[140px]">
                        <x-admin::flat-picker.date ::allow-input="false">
                            <input type="text" v-model="filters.date_to" placeholder="To" class="w-full rounded-md border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                        </x-admin::flat-picker.date>
                    </div>
                    <button type="button" @click="applyFilters" class="secondary-button whitespace-nowrap">Apply</button>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-leads-filters', {
                template: '#v-leads-filters-template',
                
                data() {
                    return {
                        presets: {
                            today: 'Today',
                            yesterday: 'Yesterday',
                            this_week: 'This Week',
                            this_month: 'This Month',
                            last_30_days: 'Last 30 Days',
                            last_month: 'Last Month',
                            this_year: 'This Year',
                            all: 'All Time'
                        },
                        filters: {
                            date_range: "{{ request('date_range', 'last_30_days') }}",
                            date_from: "{{ request('date_from', '') }}",
                            date_to: "{{ request('date_to', '') }}"
                        }
                    }
                },

                computed: {
                    currentLabel() {
                        if (this.filters.date_range === 'date_wise') {
                            return 'Custom Range';
                        }
                        return this.presets[this.filters.date_range] || 'Last 30 Days';
                    }
                },

                methods: {
                    selectPreset(value) {
                        this.filters.date_range = value;
                        if (value !== 'date_wise') {
                            this.applyFilters();
                        }
                    },
                    
                    applyFilters() {
                        const url = new URL(window.location.href);
                        
                        if (this.filters.date_range) {
                            url.searchParams.set('date_range', this.filters.date_range);
                        } else {
                            url.searchParams.delete('date_range');
                        }
                        
                        if (this.filters.date_range === 'date_wise' && this.filters.date_from) {
                            url.searchParams.set('date_from', this.filters.date_from);
                        } else {
                            url.searchParams.delete('date_from');
                        }
                        
                        if (this.filters.date_range === 'date_wise' && this.filters.date_to) {
                            url.searchParams.set('date_to', this.filters.date_to);
                        } else {
                            url.searchParams.delete('date_to');
                        }

                        window.history.pushState({}, '', url);

                        this.$emitter.emit('refresh-datagrid');
                        this.$emitter.emit('refresh-kanban');
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
