@pushOnce('scripts')
    <script type="text/x-template" id="v-person-campaign-interests-template">
        <div class="animate-[on-fade_0.5s_ease-in-out] p-4">
            <div v-if="isLoading" class="flex flex-col gap-3">
                <div class="shimmer h-10 w-full rounded"></div>
                <div class="shimmer h-10 w-full rounded"></div>
            </div>

            <p
                v-else-if="! campaigns.length"
                class="text-sm text-gray-500 dark:text-gray-400"
            >
                @lang('admin::app.contacts.persons.view.campaigns.empty')
            </p>

            <div v-else class="flex flex-col gap-2">
                <div
                    v-for="campaign in campaigns"
                    :key="campaign.id"
                    class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800"
                >
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-gray-50 dark:hover:bg-gray-950"
                        v-on:click="toggleCampaign(campaign.id)"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-brandColor">
                                @{{ campaign.campaign || campaign.title || unknownCampaign }}
                            </p>

                            <p
                                v-if="campaign.stage || campaign.source"
                                class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400"
                            >
                                <span v-if="campaign.stage">@{{ campaign.stage }}</span>
                                <span v-if="campaign.stage && campaign.source"> · </span>
                                <span v-if="campaign.source">@{{ campaign.source }}</span>
                            </p>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                @{{ campaign.relative }}
                            </span>

                            <span
                                class="icon-arrow-down text-lg text-gray-400 transition"
                                :class="{ 'rotate-180': expandedId === campaign.id }"
                            ></span>
                        </div>
                    </button>

                    <div
                        v-if="expandedId === campaign.id"
                        class="border-t border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-950"
                    >
                        <div v-if="detailLoading[campaign.id]" class="flex flex-col gap-2">
                            <div class="shimmer h-4 w-full rounded"></div>
                            <div class="shimmer h-4 w-2/3 rounded"></div>
                        </div>

                        <div v-else-if="details[campaign.id]">
                            <p
                                v-if="details[campaign.id].title"
                                class="mb-2 text-sm font-medium dark:text-white"
                            >
                                @{{ details[campaign.id].title }}
                            </p>

                            <p
                                v-if="details[campaign.id].description"
                                class="whitespace-pre-wrap text-sm leading-relaxed text-gray-700 dark:text-gray-300"
                            >
                                @{{ details[campaign.id].description }}
                            </p>

                            <p
                                v-else
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                @lang('admin::app.contacts.persons.view.campaigns.no-description')
                            </p>

                            <a
                                :href="details[campaign.id].view_url"
                                class="mt-3 inline-flex text-sm font-medium text-brandColor hover:underline"
                                target="_blank"
                            >
                                @lang('admin::app.contacts.persons.view.campaigns.view-lead')
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-person-campaign-interests', {
            template: '#v-person-campaign-interests-template',

            props: {
                listEndpoint: {
                    type: String,
                    required: true,
                },
                detailEndpoint: {
                    type: String,
                    required: true,
                },
            },

            data() {
                return {
                    isLoading: false,
                    campaigns: [],
                    expandedId: null,
                    details: {},
                    detailLoading: {},
                    unknownCampaign: @json(trans('admin::app.contacts.persons.view.campaigns.unknown-campaign')),
                };
            },

            mounted() {
                this.$nextTick(() => {
                    window.setTimeout(() => this.loadCampaigns(), 150);
                });
            },

            methods: {
                loadCampaigns() {
                    this.isLoading = true;

                    this.$axios.get(this.listEndpoint)
                        .then(response => {
                            this.campaigns = response.data.data ?? [];
                        })
                        .catch(() => {
                            this.campaigns = [];
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
                },

                toggleCampaign(id) {
                    if (this.expandedId === id) {
                        this.expandedId = null;

                        return;
                    }

                    this.expandedId = id;

                    if (this.details[id]) {
                        return;
                    }

                    this.detailLoading[id] = true;

                    this.$axios.get(this.detailEndpoint.replace('__lead__', id))
                        .then(response => {
                            this.details[id] = response.data.data;
                        })
                        .catch(() => {
                            this.details[id] = { description: null };
                        })
                        .finally(() => {
                            this.detailLoading[id] = false;
                        });
                },
            },
        });
    </script>
@endPushOnce
