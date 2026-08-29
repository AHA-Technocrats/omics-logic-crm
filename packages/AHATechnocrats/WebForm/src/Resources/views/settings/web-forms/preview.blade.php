@php
    $honeypotRejectMessage = config(
        'omicslogic.anti_spam.honeypot_reject_message',
        'Do not fill the data in the person and organisation.'
    );
@endphp

<x-web_form::layouts>
    <x-slot:title>
        {{ strip_tags($webForm->title) }}
    </x-slot>

    <!-- Web Form -->
    <v-web-form>
        <div class="flex h-[100vh] items-center justify-center">
            <div class="flex flex-col items-center gap-5">
                <x-web_form::spinner />
            </div>
        </div>
    </v-web-form>

    @push('styles')
        <style>
            .webform-shell {
                display: flex;
                min-height: 100vh;
                align-items: center;
                justify-content: center;
                padding: 48px 16px;
                box-sizing: border-box;
                background:
                    radial-gradient(ellipse at top, rgba(14, 165, 233, 0.12), transparent 55%),
                    linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
            }

            /* When embedded, the iframe is resized to fit; don't force a tall viewport. */
            body.is-embedded .webform-shell {
                min-height: 0;
            }

            .webform-inner {
                display: flex;
                width: 100%;
                max-width: 560px;
                flex-direction: column;
                align-items: center;
                gap: 24px;
            }

            .webform-card {
                width: 100%;
                border: none;
                border-radius: 16px;
                background: #fff;
                padding: 0;
                overflow: hidden;
                box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
                box-sizing: border-box;
            }

            .webform-header {
                margin: 0;
                padding: 26px 28px 22px;
                border-bottom: none;
                text-align: center;
                color: #fff;
            }

            .webform-title {
                margin: 0 0 8px;
                font-size: 26px;
                line-height: 1.25;
                font-weight: 700;
                letter-spacing: -0.02em;
                color: inherit !important;
            }

            .webform-description {
                margin: 0;
                font-size: 14px;
                line-height: 1.6;
                color: rgba(255, 255, 255, 0.85);
            }

            .webform-body {
                padding: 20px 24px 26px;
            }

            .webform-description p {
                margin: 0 0 0.75em;
            }

            .webform-description p:last-child {
                margin-bottom: 0;
            }

            .webform-description ul,
            .webform-description ol {
                margin: 0 0 0.75em 1.25em;
                padding: 0;
            }

            .webform-hp-trap {
                position: absolute !important;
                left: -9999px !important;
                height: 0 !important;
                width: 0 !important;
                opacity: 0 !important;
                overflow: hidden !important;
                pointer-events: none !important;
            }

            .webform-field {
                margin-bottom: 18px;
                padding: 0;
                border: none;
                border-radius: 0;
                background: transparent;
            }

            .webform-field:last-child {
                margin-bottom: 0;
            }

            .webform-field .mb-4 {
                margin-bottom: 0 !important;
            }

            .webform-field label {
                display: block;
                margin-bottom: 8px;
                font-size: 14px;
                font-weight: 600;
                color: #334155;
            }

            .webform-field input,
            .webform-field select,
            .webform-field textarea {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                border-radius: 10px !important;
                border: 1px solid #dbe3f0 !important;
                padding: 12px 14px !important;
                font-size: 15px !important;
                line-height: 1.4 !important;
                background: #fff !important;
                transition: border-color 0.15s ease, box-shadow 0.15s ease;
            }

            .webform-field input:focus,
            .webform-field select:focus,
            .webform-field textarea:focus {
                border-color: #0ea5e9 !important;
                box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15) !important;
                outline: none !important;
            }

            .webform-actions {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
                margin-top: 28px;
            }

            .webform-actions .primary-button {
                width: 100%;
                min-width: 0;
                padding: 14px 24px;
                border-radius: 10px;
                font-size: 16px;
                font-weight: 600;
            }

            .webform-privacy {
                margin: 0;
                padding: 12px 14px;
                border-radius: 10px;
                background: #f8fafc;
                text-align: center;
                font-size: 12.5px;
                line-height: 1.5;
                color: #64748b;
            }

            .dark .webform-shell {
                background:
                    radial-gradient(ellipse at top, rgba(14, 165, 233, 0.18), transparent 55%),
                    linear-gradient(180deg, #0f172a 0%, #111827 100%);
            }

            .dark .webform-card,
            .dark .webform-field {
                background: #111827;
                border-color: transparent;
            }

            .dark .webform-privacy {
                background: #0f172a;
                color: #94a3b8;
            }

            .dark .webform-field label {
                color: #e2e8f0;
            }

            .dark .webform-field input,
            .dark .webform-field select,
            .dark .webform-field textarea {
                background: #0f172a !important;
                border-color: #334155 !important;
                color: #f8fafc !important;
            }
        </style>
    @endpush

    @pushOnce('scripts')
        <script
            type="text/template"
            id="v-web-form-template"
        >
            <div
                class="webform-shell"
                style="background-color: {{ $webForm->background_color }}"
            >
                <div class="webform-inner">
                    <div
                        class="webform-card"
                        style="background-color: {{ $webForm->form_background_color }}"
                    >
                        <div
                            class="webform-header"
                            style="background: linear-gradient(135deg, {{ $webForm->form_title_color }} 0%, {{ $webForm->form_submit_button_color }} 100%);"
                        >
                            <h1 class="webform-title">
                                {{ $webForm->title }}
                            </h1>

                            @if ($webForm->description)
                                <div class="webform-description">{!! $webForm->description !!}</div>
                            @endif
                        </div>

                        <div class="webform-body">
                        {!! view_render_event('web_forms.web_forms.form_controls.before', ['webForm' => $webForm]) !!}

                        <x-web_form::form
                            v-slot="{ meta, values, errors, handleSubmit }"
                            as="div"
                            ref="modalForm"
                        >
                            <form
                                @submit="handleSubmit($event, create)"
                                ref="webForm"
                            >
                                @include('web_form::settings.web-forms.controls')

                                <input type="hidden" name="_form_token" value="{{ $formToken ?? '' }}" />

                                <input
                                    type="text"
                                    name="{{ config('omicslogic.anti_spam.honeypot_field', '_website_url') }}"
                                    tabindex="-1"
                                    autocomplete="off"
                                    aria-hidden="true"
                                    class="webform-hp-trap"
                                />

                                @if ($webForm->honeypot_enabled)
                                    <div class="webform-hp-trap" aria-hidden="true">
                                        <label for="persons_hp_name">Person</label>
                                        <input
                                            type="text"
                                            id="persons_hp_name"
                                            name="persons_hp[name]"
                                            tabindex="-1"
                                            autocomplete="off"
                                        />

                                        <label for="organizations_hp_name">Organisation</label>
                                        <input
                                            type="text"
                                            id="organizations_hp_name"
                                            name="organizations_hp[name]"
                                            tabindex="-1"
                                            autocomplete="off"
                                        />
                                    </div>
                                @endif

                                @if ($webForm->turnstile_enabled && config('omicslogic.turnstile.site_key'))
                                    <div
                                        class="cf-turnstile mb-3"
                                        data-sitekey="{{ config('omicslogic.turnstile.site_key') }}"
                                    ></div>
                                @endif

                                <div class="webform-actions">
                                    <x-web_form::button
                                        class="primary-button rounded text-white font-semibold transition-all hover:opacity-90"
                                        :title="$webForm->submit_button_label"
                                        ::loading="isStoring"
                                        ::disabled="isStoring"
                                        style="background-color: {{ $webForm->form_submit_button_color }} !important"
                                    />

                                    <p class="webform-privacy">
                                        &#128274; <strong>Your data is safe.</strong> We respect your privacy and will never share your information.
                                    </p>
                                </div>
                            </form>
                        </x-web_form::form>

                        {!! view_render_event('web_forms.web_forms.form_controls.after', ['webForm' => $webForm]) !!}
                        </div>
                    </div>
                </div>
            </div>
        </script>

        <script>
            /**
             * Only run when this page is loaded inside the embed iframe. Report the
             * form's real height to the parent so the iframe can size to content
             * instead of a fixed height that leaves blank space or clips.
             */
            (function () {
                if (window.parent === window) {
                    return;
                }

                document.body.classList.add('is-embedded');

                var lastHeight = 0;

                function reportHeight() {
                    var shell = document.querySelector('.webform-shell');
                    var height = Math.ceil(
                        shell ? shell.scrollHeight : document.documentElement.scrollHeight
                    );

                    if (! height || height === lastHeight) {
                        return;
                    }

                    lastHeight = height;

                    /**
                     * The parent may live on any domain, so its origin is unknown here.
                     * Only a height number is sent, and the embed script validates that
                     * the message came from this app before acting on it.
                     */
                    window.parent.postMessage(
                        { type: 'omicslogic-webform-height', height: height },
                        '*'
                    );
                }

                window.addEventListener('load', reportHeight);
                window.addEventListener('resize', reportHeight);
                document.addEventListener('transitionend', reportHeight);

                if (window.ResizeObserver) {
                    var observer = new ResizeObserver(reportHeight);

                    observer.observe(document.body);

                    var shell = document.querySelector('.webform-shell');

                    if (shell) {
                        observer.observe(shell);
                    }
                }

                setInterval(reportHeight, 500);
            })();
        </script>

        <script type="module">
            app.component('v-web-form', {
                template: '#v-web-form-template',

                data() {
                    return {
                        isStoring: false,
                        programInterest: '',
                        organizationSuggestions: [],
                        showOrganizationSuggestions: false,
                        selectedOrganizationId: '',
                        organizationSearchTimer: null,
                        honeypotRejectMessage: @json($honeypotRejectMessage),
                        selectedOrganizationCountry: '',
                        showManualOrgModal: false,
                        manualOrgName: '',
                        manualOrgCountry: '',
                        manualOrgType: '',
                        manualOrgWebsite: '',
                        selectedOrganizationType: '',
                        selectedOrganizationWebsite: '',
                        organizationSearchTimeout: null,
                        isSelectingOrganization: false,
                    };
                },

                methods: {
                    organizationSearchUrl() {
                        return '{{ route('admin.settings.web_forms.organizations.search') }}';
                    },

                    checkEmailUrl() {
                        return '{{ route('admin.settings.web_forms.check_email', $webForm->id) }}';
                    },

                    onOrganizationInput(event, field) {
                        if (this.isSelectingOrganization) {
                            return;
                        }

                        if (field?.onChange) {
                            field.onChange(event);
                        }

                        this.selectedOrganizationId = '';

                        const query = event?.target?.value ?? '';

                        if (this.organizationSearchTimer) {
                            clearTimeout(this.organizationSearchTimer);
                        }

                        if (query.trim().length < 2) {
                            this.organizationSuggestions = [];
                            this.showOrganizationSuggestions = false;

                            return;
                        }

                        this.organizationSearchTimer = setTimeout(() => {
                            this.fetchOrganizationSuggestions(query);
                        }, 350);
                    },

                    fetchOrganizationSuggestions(query) {
                        this.$axios.get(this.organizationSearchUrl(), {
                            params: { q: query },
                        }).then((response) => {
                            this.organizationSuggestions = response.data.data || [];
                            this.showOrganizationSuggestions = true;
                        });
                    },

                    selectOrganization(organization) {
                        this.isSelectingOrganization = true;
                        
                        this.selectedOrganizationId = String(organization.id);
                        this.selectedOrganizationCountry = String(organization.country_code || '');
                        this.selectedOrganizationType = String(organization.type || '');
                        this.selectedOrganizationWebsite = String(organization.website || '');

                        const input = this.$refs.webForm?.querySelector('[name="persons[organization_name]"]');

                        if (input) {
                            input.value = organization.name;
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        }

                        this.organizationSuggestions = [];
                        this.showOrganizationSuggestions = false;
                        
                        setTimeout(() => {
                            this.isSelectingOrganization = false;
                        }, 100);
                    },

                    hideOrganizationSuggestions() {
                        setTimeout(() => {
                            // Only hide if the manual modal is not being opened
                            if (!this.showManualOrgModal) {
                                this.showOrganizationSuggestions = false;
                            }
                        }, 150);
                    },

                    openManualOrgModal() {
                        this.showManualOrgModal = true;
                        this.showOrganizationSuggestions = false;
                        
                        // Pre-fill the name with what the user already typed
                        const input = this.$refs.webForm?.querySelector('[name="persons[organization_name]"]');
                        if (input) {
                            this.manualOrgName = input.value;
                        }
                    },

                    closeManualOrgModal() {
                        this.showManualOrgModal = false;
                    },

                    saveManualOrg() {
                        if (!this.manualOrgName || !this.manualOrgType) {
                            this.$emitter.emit('add-flash', {
                                type: 'warning',
                                message: 'Please enter the name and type.',
                            });
                            return;
                        }

                        const personCountryInput = this.$refs.webForm?.querySelector('[name="persons[country_code]"]');
                        const personCountry = personCountryInput?.value || '';
                        
                        this.selectedOrganizationId = ''; 
                        this.selectedOrganizationCountry = personCountry;
                        this.selectedOrganizationType = this.manualOrgType;
                        this.selectedOrganizationWebsite = this.manualOrgWebsite;
                        
                        this.isSelectingOrganization = true;
                        
                        const input = this.$refs.webForm?.querySelector('[name="persons[organization_name]"]');
                        if (input) {
                            input.value = this.manualOrgName;
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        
                        this.showOrganizationSuggestions = false;
                        this.closeManualOrgModal();
                        
                        setTimeout(() => {
                            this.isSelectingOrganization = false;
                        }, 100);
                    },

                    honeypotsFilled(formData) {
                        const personHp = (formData.get('persons_hp[name]') || '').toString().trim();
                        const orgHp = (formData.get('organizations_hp[name]') || '').toString().trim();
                        const websiteHp = (formData.get('{{ config('omicslogic.anti_spam.honeypot_field', '_website_url') }}') || '').toString().trim();

                        return personHp !== '' || orgHp !== '' || websiteHp !== '';
                    },

                    extractEmail(formData) {
                        return (formData.get('persons[emails][0][value]') || '').toString().trim().toLowerCase();
                    },

                    async confirmResubmitIfNeeded(email) {
                        if (! email) {
                            return true;
                        }

                        try {
                            const response = await this.$axios.post(this.checkEmailUrl(), { email });

                            if (! response.data?.already_submitted) {
                                return true;
                            }

                            return window.confirm(
                                'A submission already exists for this email. Do you want to send again?'
                            );
                        } catch (error) {
                            return true;
                        }
                    },

                    async create(params, { resetForm, setErrors }) {
                        const formData = new FormData(this.$refs.webForm);

                        if (this.honeypotsFilled(formData)) {
                            this.$emitter.emit('add-flash', {
                                type: 'warning',
                                message: this.honeypotRejectMessage,
                            });

                            return;
                        }

                        this.isStoring = true;

                        try {
                            const email = this.extractEmail(formData);
                            const shouldContinue = await this.confirmResubmitIfNeeded(email);

                            if (! shouldContinue) {
                                return;
                            }

                            let inputNames = Array.from(formData.keys());

                            inputNames = inputNames.reduce((acc, name) => {
                                const dotName = name.replace(/\[([^\]]+)\]/g, '.$1');

                                acc[dotName] = name;

                                return acc;
                            }, {});

                            const response = await this.$axios.post(
                                '{{ route('admin.settings.web_forms.form_store', $webForm->id) }}',
                                formData,
                                {
                                    headers: {
                                        'Content-Type': 'multipart/form-data',
                                    },
                                }
                            );

                            if (response.data?.redirect) {
                                window.location.href = response.data.redirect;

                                return;
                            }

                            if (response.data?.message) {
                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data.message,
                                });
                            }
                        } catch (error) {
                            if (error.response?.data?.redirect) {
                                window.location.href = error.response.data.redirect;

                                return;
                            }

                            if (! error.response?.data?.errors) {
                                const message = error.response?.data?.message
                                    || error.response?.data?.errors?.form?.[0]
                                    || 'Something went wrong. Please try again.';

                                this.$emitter.emit('add-flash', { type: 'error', message });

                                return;
                            }

                            const laravelErrors = error.response.data.errors || {};
                            const mappedErrors = {};

                            let inputNames = Array.from(new FormData(this.$refs.webForm).keys());

                            inputNames = inputNames.reduce((acc, name) => {
                                const dotName = name.replace(/\[([^\]]+)\]/g, '.$1');

                                acc[dotName] = name;

                                return acc;
                            }, {});

                            for (const [dotKey, messages] of Object.entries(laravelErrors)) {
                                const inputName = inputNames[dotKey];

                                if (inputName && messages.length) {
                                    mappedErrors[inputName] = messages[0];
                                }
                            }

                            if (laravelErrors.form?.[0] && ! Object.keys(mappedErrors).length) {
                                this.$emitter.emit('add-flash', {
                                    type: 'warning',
                                    message: laravelErrors.form[0],
                                });
                            }

                            setErrors(mappedErrors);
                        } finally {
                            this.isStoring = false;
                        }
                    }
                }
            });
        </script>
    @endPushOnce
</x-web_form::layouts>
