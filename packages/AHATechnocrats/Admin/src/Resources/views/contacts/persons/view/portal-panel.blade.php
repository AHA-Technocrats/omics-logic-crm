@pushOnce('scripts')
    <script type="text/x-template" id="v-person-portal-panel-template">
        <div class="animate-[on-fade_0.5s_ease-in-out]">
            <!-- Loading -->
            <div v-if="isLoading" class="flex flex-col gap-4 p-4">
                <div class="flex items-center gap-4">
                    <div class="shimmer h-16 w-16 rounded-full"></div>
                    <div class="flex flex-1 flex-col gap-2">
                        <div class="shimmer h-5 w-2/3 rounded"></div>
                        <div class="shimmer h-4 w-1/2 rounded"></div>
                    </div>
                </div>
                <div class="shimmer h-8 w-full rounded"></div>
                <div class="shimmer h-24 w-full rounded"></div>
            </div>

            <!-- Waiting for background load -->
            <div
                v-else-if="! portalData"
                class="p-4 text-sm text-gray-400 dark:text-gray-500"
            >
                @lang('admin::app.contacts.persons.view.portal.loading')
            </div>

            <!-- Not on portal -->
            <div
                v-else-if="! portalData.success"
                class="p-4"
            >
                <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-100">
                    @{{ portalData.message || notFoundMessage }}
                </div>
            </div>

            <!-- Portal profile card -->
            <template v-else>
                <!-- Header -->
                <div class="border-b border-gray-200 bg-gradient-to-br from-slate-50 to-white p-4 dark:border-gray-800 dark:from-gray-950 dark:to-gray-900">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        @lang('admin::app.contacts.persons.view.portal.profile-title')
                    </p>

                    <div class="flex items-start gap-4">
                        <div class="relative shrink-0">
                            <img
                                v-if="avatarUrl"
                                :src="avatarUrl"
                                :alt="displayName"
                                class="h-16 w-16 rounded-full border-2 border-white object-cover shadow-sm dark:border-gray-800"
                                v-on:error="onAvatarError"
                            />

                            <div
                                v-else
                                class="flex h-16 w-16 items-center justify-center rounded-full border-2 border-white bg-brandColor text-lg font-semibold text-white shadow-sm dark:border-gray-800"
                            >
                                @{{ initials }}
                            </div>

                            <span
                                class="absolute -bottom-0.5 -right-0.5 flex h-5 w-5 items-center justify-center rounded-full border-2 border-white bg-green-500 dark:border-gray-900"
                                title="On portal"
                            >
                                <span class="icon-tick text-[10px] text-white"></span>
                            </span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h4 class="truncate text-base font-bold dark:text-white">
                                @{{ displayName }}
                            </h4>

                            <p v-if="displayEmail" class="truncate text-sm text-gray-600 dark:text-gray-300">
                                @{{ displayEmail }}
                            </p>

                            <p
                                v-if="headline"
                                class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                            >
                                @{{ headline }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="stats.length"
                        class="mt-4 grid grid-cols-3 gap-2"
                    >
                        <div
                            v-for="stat in stats"
                            :key="stat.label"
                            class="rounded-lg border border-gray-200 bg-white/80 px-2 py-2 text-center dark:border-gray-800 dark:bg-gray-900/60"
                        >
                            <p class="text-sm font-bold text-brandColor">@{{ stat.value }}</p>
                            <p class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">@{{ stat.label }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-gray-200 dark:border-gray-800">
                    <button
                        v-for="tab in tabs"
                        :key="tab.name"
                        type="button"
                        class="cursor-pointer px-4 py-2.5 text-sm font-medium dark:text-white"
                        :class="selectedTab === tab.name
                            ? 'border-brandColor border-b-2 text-brandColor'
                            : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                        v-on:click="selectedTab = tab.name"
                    >
                        @{{ tab.label }}
                        <span
                            v-if="tab.name === 'activity' && timeline.length"
                            class="ml-1 rounded-full bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                        >
                            @{{ timeline.length }}
                        </span>
                        <span
                            v-if="tab.name === 'purchases' && purchaseHistory.length"
                            class="ml-1 rounded-full bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                        >
                            @{{ purchaseHistory.length }}
                        </span>
                    </button>
                </div>

                <!-- Profile tab -->
                <div v-show="selectedTab === 'profile'" class="p-4">
                    <div v-if="bio" class="mb-4 rounded-lg bg-gray-50 p-3 text-sm leading-relaxed text-gray-700 dark:bg-gray-950 dark:text-gray-300">
                        @{{ bio }}
                    </div>

                    <div class="flex flex-col gap-3 text-sm">
                        <div
                            v-for="field in profileFields"
                            :key="field.label"
                            class="flex justify-between gap-4 border-b border-gray-100 pb-3 last:border-b-0 dark:border-gray-800"
                        >
                            <span class="text-gray-600 dark:text-gray-300">@{{ field.label }}</span>

                            <a
                                v-if="field.isLink"
                                :href="field.value"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="max-w-[55%] truncate text-right font-medium text-brandColor hover:underline"
                            >
                                @{{ field.display }}
                            </a>

                            <span
                                v-else
                                class="max-w-[55%] truncate text-right font-medium dark:text-white"
                                :title="field.value"
                            >
                                @{{ field.value }}
                            </span>
                        </div>
                    </div>

                    <p
                        v-if="! profileFields.length && ! bio"
                        class="text-sm text-gray-500 dark:text-gray-400"
                    >
                        @lang('admin::app.contacts.persons.view.portal.profile-empty')
                    </p>
                </div>

                <!-- Activity tab -->
                <div v-show="selectedTab === 'activity'" class="p-4">
                    <div v-if="! timeline.length" class="text-sm text-gray-500 dark:text-gray-400">
                        @lang('admin::app.contacts.persons.view.portal.timeline-empty')
                    </div>

                    <div v-else class="flex max-h-[420px] flex-col gap-0 overflow-y-auto pr-1">
                        <div
                            v-for="(event, index) in timeline"
                            :key="event.id || index"
                            class="flex gap-3"
                        >
                            <div class="flex flex-col items-center">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-full"
                                    :class="event.icon_class"
                                >
                                    <span :class="event.icon" class="text-lg"></span>
                                </div>

                                <div
                                    v-if="index < timeline.length - 1"
                                    class="my-1 w-px flex-1 bg-gray-200 dark:bg-gray-700"
                                ></div>
                            </div>

                            <div class="flex min-w-0 flex-1 items-start justify-between gap-3 pb-5">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold dark:text-white">@{{ event.title }}</p>

                                    <p
                                        v-if="event.detail"
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        @{{ event.detail }}
                                    </p>

                                    <div
                                        v-if="event.quote"
                                        class="mt-2 rounded-md bg-blue-50 px-3 py-2 text-xs italic text-blue-900 dark:bg-blue-950/30 dark:text-blue-100"
                                    >
                                        “@{{ event.quote }}”
                                    </div>
                                </div>

                                <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">
                                    @{{ event.relative || event.absolute }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase history tab -->
                <div v-show="selectedTab === 'purchases'" class="p-4">
                    <div v-if="! purchaseHistory.length" class="text-sm text-gray-500 dark:text-gray-400">
                        @lang('admin::app.contacts.persons.view.portal.purchases-empty')
                    </div>

                    <div v-else class="flex max-h-[420px] flex-col gap-3 overflow-y-auto pr-1">
                        <div
                            v-for="(purchase, index) in purchaseHistory"
                            :key="purchase.id || index"
                            class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-950"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="icon-cart text-lg text-teal-600 dark:text-teal-400"></span>
                                        <p class="truncate text-sm font-semibold dark:text-white">
                                            @{{ purchase.title }}
                                        </p>
                                    </div>

                                    <p
                                        v-if="purchase.detail"
                                        class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        @{{ purchase.detail }}
                                    </p>

                                    <p
                                        v-if="purchase.order_id"
                                        class="mt-1 text-xs text-gray-400 dark:text-gray-500"
                                    >
                                        @lang('admin::app.contacts.persons.view.portal.purchase-order'):
                                        @{{ purchase.order_id }}
                                    </p>
                                </div>

                                <div class="shrink-0 text-right">
                                    <p
                                        v-if="purchase.amount_label"
                                        class="text-sm font-bold text-brandColor"
                                    >
                                        @{{ purchase.amount_label }}
                                    </p>

                                    <span
                                        class="mt-1 inline-block rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase"
                                        :class="purchaseStatusClass(purchase.status)"
                                    >
                                        @{{ formatPurchaseStatus(purchase.status) }}
                                    </span>

                                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                        @{{ purchase.relative || purchase.absolute }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </script>

    <script type="module">
        app.component('v-person-portal-panel', {
            template: '#v-person-portal-panel-template',

            props: {
                personName: {
                    type: String,
                    default: '',
                },
                personEmail: {
                    type: String,
                    default: '',
                },
            },

            data() {
                return {
                    isLoading: false,
                    portalData: null,
                    selectedTab: 'profile',
                    avatarBroken: false,
                    notFoundMessage: @json(trans('admin::app.contacts.persons.view.portal.not-found')),
                    tabs: [
                        {
                            name: 'profile',
                            label: @json(trans('admin::app.contacts.persons.view.portal.tab-profile')),
                        },
                        {
                            name: 'activity',
                            label: @json(trans('admin::app.contacts.persons.view.portal.tab-activity')),
                        },
                        {
                            name: 'purchases',
                            label: @json(trans('admin::app.contacts.persons.view.portal.tab-purchases')),
                        },
                    ],
                    profileFieldMap: [
                        { key: 'occupation', label: @json(trans('admin::app.contacts.persons.view.portal.fields.occupation')) },
                        { key: 'organization', label: @json(trans('admin::app.contacts.persons.view.portal.fields.organization')) },
                        { key: 'country', label: @json(trans('admin::app.contacts.persons.view.portal.fields.country')) },
                        { key: 'phone', label: @json(trans('admin::app.contacts.persons.view.portal.fields.phone')) },
                        { key: 'joinDate', label: @json(trans('admin::app.contacts.persons.view.portal.fields.join-date')) },
                        { key: 'programsJoined', label: @json(trans('admin::app.contacts.persons.view.portal.fields.programs')) },
                        { key: 'linkedInLink', label: 'LinkedIn', isLink: true },
                        { key: 'twitterLink', label: 'Twitter', isLink: true },
                        { key: 'facebookLink', label: 'Facebook', isLink: true },
                        { key: 'instagramLink', label: 'Instagram', isLink: true },
                        { key: 'orcidLink', label: 'ORCID', isLink: true },
                    ],
                    statFieldMap: [
                        { key: 'totalAchievements', label: @json(trans('admin::app.contacts.persons.view.portal.stats.achievements')) },
                        { key: 'points', label: @json(trans('admin::app.contacts.persons.view.portal.stats.points')) },
                        { key: 'totalCertificates', label: @json(trans('admin::app.contacts.persons.view.portal.stats.certificates')) },
                    ],
                };
            },

            computed: {
                user() {
                    return this.portalData?.data?.user ?? {};
                },

                timeline() {
                    return this.portalData?.data?.timeline ?? [];
                },

                purchaseHistory() {
                    return this.portalData?.data?.purchase_history ?? [];
                },

                displayName() {
                    return this.user.name || this.personName;
                },

                displayEmail() {
                    return this.user.email || this.personEmail;
                },

                headline() {
                    const parts = [this.user.occupation, this.user.organization].filter(Boolean);

                    return parts.join(' @@ ');
                },

                bio() {
                    return this.user.bio || null;
                },

                avatarUrl() {
                    if (this.avatarBroken) {
                        return null;
                    }

                    const avatar = this.user.avatar;

                    if (! avatar || typeof avatar !== 'string') {
                        return null;
                    }

                    if (avatar.startsWith('http://') || avatar.startsWith('https://')) {
                        return avatar;
                    }

                    if (avatar.startsWith('//')) {
                        return `https:${avatar}`;
                    }

                    if (avatar.startsWith('/')) {
                        return avatar;
                    }

                    return avatar;
                },

                initials() {
                    return this.displayName
                        .split(' ')
                        .filter(Boolean)
                        .slice(0, 2)
                        .map(word => word[0]?.toUpperCase() ?? '')
                        .join('') || '?';
                },

                stats() {
                    return this.statFieldMap
                        .map(stat => ({
                            label: stat.label,
                            value: this.user[stat.key],
                        }))
                        .filter(stat => stat.value !== null && stat.value !== '' && stat.value !== undefined);
                },

                profileFields() {
                    return this.profileFieldMap
                        .map(field => {
                            const value = this.user[field.key];

                            if (value === null || value === '' || value === undefined) {
                                return null;
                            }

                            const formatted = this.formatFieldValue(value);

                            return {
                                label: field.label,
                                value: formatted,
                                display: field.isLink ? this.formatLinkLabel(formatted) : formatted,
                                isLink: Boolean(field.isLink),
                            };
                        })
                        .filter(Boolean);
                },
            },

            mounted() {
                this.$emitter.on('person-portal-loading', this.onLoading);
                this.$emitter.on('person-portal-loaded', this.onLoaded);
                this.$emitter.emit('person-portal-request');
            },

            beforeUnmount() {
                this.$emitter.off('person-portal-loading', this.onLoading);
                this.$emitter.off('person-portal-loaded', this.onLoaded);
            },

            methods: {
                onLoading(isLoading) {
                    this.isLoading = isLoading;
                },

                onLoaded(data) {
                    this.portalData = data;
                    this.avatarBroken = false;
                },

                onAvatarError() {
                    this.avatarBroken = true;
                },

                formatFieldValue(value) {
                    if (Array.isArray(value)) {
                        return value.filter(Boolean).join(', ');
                    }

                    if (typeof value === 'object') {
                        return JSON.stringify(value);
                    }

                    return String(value);
                },

                formatLinkLabel(url) {
                    try {
                        const parsed = new URL(url.startsWith('http') ? url : `https://${url}`);

                        return parsed.hostname.replace('www.', '');
                    } catch (error) {
                        return url;
                    }
                },

                formatPurchaseStatus(status) {
                    if (! status) {
                        return @json(trans('admin::app.contacts.persons.view.portal.purchase-status-unknown'));
                    }

                    return status.replace(/_/g, ' ');
                },

                purchaseStatusClass(status) {
                    const normalized = String(status || '').toLowerCase();

                    if (['completed', 'paid', 'success', 'successful'].includes(normalized)) {
                        return 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300';
                    }

                    if (['pending', 'processing', 'in_progress'].includes(normalized)) {
                        return 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300';
                    }

                    if (['failed', 'cancelled', 'canceled', 'refunded', 'declined'].includes(normalized)) {
                        return 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300';
                    }

                    return 'bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300';
                },
            },
        });
    </script>
@endPushOnce
