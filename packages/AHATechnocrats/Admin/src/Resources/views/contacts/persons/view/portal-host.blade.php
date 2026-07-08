<v-person-portal-host
    endpoint="{{ route('admin.contacts.persons.portal', $person->id) }}"
></v-person-portal-host>

@pushOnce('scripts')
    <script type="text/x-template" id="v-person-portal-host-template">
        <div class="hidden"></div>
    </script>

    <script type="module">
        app.component('v-person-portal-host', {
            template: '#v-person-portal-host-template',

            props: {
                endpoint: {
                    type: String,
                    required: true,
                },
            },

            data() {
                return {
                    isLoading: false,
                    portalData: null,
                    error: false,
                    hasStarted: false,
                };
            },

            mounted() {
                this.$emitter.on('person-portal-request', this.publishState);

                // Let CRM activities render first; portal data loads in the background.
                this.$nextTick(() => {
                    window.setTimeout(() => this.loadPortalData(), 100);
                });
            },

            beforeUnmount() {
                this.$emitter.off('person-portal-request', this.publishState);
            },

            methods: {
                publishState() {
                    this.$emitter.emit('person-portal-loading', this.isLoading);

                    if (this.portalData !== null) {
                        this.$emitter.emit('person-portal-loaded', this.portalData);
                    }
                },

                loadPortalData() {
                    if (this.hasStarted) {
                        return;
                    }

                    this.hasStarted = true;
                    this.isLoading = true;
                    this.error = false;
                    this.$emitter.emit('person-portal-loading', this.isLoading);

                    this.$axios.get(this.endpoint)
                        .then(response => {
                            this.portalData = response.data;
                            this.$emitter.emit('person-portal-loaded', this.portalData);
                        })
                        .catch(() => {
                            this.error = true;
                            this.portalData = {
                                success: false,
                                message: @json(trans('admin::app.contacts.persons.view.portal.not-found')),
                            };
                            this.$emitter.emit('person-portal-loaded', this.portalData);
                        })
                        .finally(() => {
                            this.isLoading = false;
                            this.$emitter.emit('person-portal-loading', this.isLoading);
                        });
                },
            },
        });
    </script>
@endPushOnce
