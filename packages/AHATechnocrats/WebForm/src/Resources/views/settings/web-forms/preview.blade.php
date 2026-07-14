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
            }

            .webform-inner {
                display: flex;
                width: 100%;
                max-width: 640px;
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }

            .webform-card {
                width: 100%;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                background: #fff;
                padding: 24px;
                box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
                box-sizing: border-box;
            }

            .webform-header {
                margin-bottom: 24px;
                padding-bottom: 20px;
                border-bottom: 1px solid #e5e7eb;
            }

            .webform-title {
                margin: 0 0 12px;
                font-size: 28px;
                line-height: 1.25;
                font-weight: 700;
            }

            .webform-description {
                margin: 0;
                font-size: 14px;
                line-height: 1.6;
                color: #6b7280;
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

            .webform-honeypot-warning {
                margin: 0 0 16px;
                padding: 10px 12px;
                border: 1px solid #fcd34d;
                border-radius: 8px;
                background: #fffbeb;
                color: #92400e;
                font-size: 13px;
                line-height: 1.5;
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
                margin-bottom: 16px;
                padding: 18px 20px;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                background: #fff;
            }

            .webform-field:last-child {
                margin-bottom: 0;
            }

            .webform-field .mb-4 {
                margin-bottom: 0 !important;
            }

            .webform-field label {
                display: block;
                margin-bottom: 10px;
                font-size: 15px;
                font-weight: 500;
                color: #374151;
            }

            .webform-field input,
            .webform-field select,
            .webform-field textarea {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            .webform-actions {
                display: flex;
                justify-content: flex-start;
                margin-top: 24px;
            }

            .webform-actions .primary-button {
                min-width: 140px;
                padding: 10px 24px;
            }

            .dark .webform-card,
            .dark .webform-field {
                background: #111827;
                border-color: #374151;
            }

            .dark .webform-description {
                color: #9ca3af;
            }

            .dark .webform-honeypot-warning {
                background: #422006;
                border-color: #a16207;
                color: #fde68a;
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
                    @if ($logo = core()->getConfigData('general.general.admin_logo.logo_image'))
                        <img
                            style="max-height: 64px; width: auto;"
                            src="{{ Storage::url($logo) }}"
                            alt="{{ config('app.name') }}"
                        />
                    @else
                        <img
                            style="max-height: 64px; width: auto;"
                            src="{{ vite()->asset('images/logo.svg') }}"
                            alt="{{ config('app.name') }}"
                        />
                    @endif

                    <div
                        class="webform-card"
                        style="background-color: {{ $webForm->form_background_color }}; border-top: 8px solid {{ $webForm->form_submit_button_color }}"
                    >
                        <div class="webform-header">
                            <h1
                                class="webform-title"
                                style="color: {{ $webForm->form_title_color }} !important;"
                            >
                                {{ $webForm->title }}
                            </h1>

                            @if ($webForm->description)
                                <div class="webform-description">{!! $webForm->description !!}</div>
                            @endif
                        </div>

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
                                @if ($webForm->honeypot_enabled)
                                    <p class="webform-honeypot-warning" role="note">
                                        {{ $honeypotRejectMessage }}
                                    </p>
                                @endif

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
                                </div>
                            </form>
                        </x-web_form::form>

                        {!! view_render_event('web_forms.web_forms.form_controls.after', ['webForm' => $webForm]) !!}
                    </div>
                </div>
            </div>
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
                        }, 250);
                    },

                    fetchOrganizationSuggestions(query) {
                        this.$axios.get(this.organizationSearchUrl(), {
                            params: { q: query },
                        }).then((response) => {
                            this.organizationSuggestions = response.data.data || [];
                            this.showOrganizationSuggestions = this.organizationSuggestions.length > 0;
                        });
                    },

                    selectOrganization(organization) {
                        this.selectedOrganizationId = String(organization.id);

                        const input = this.$refs.webForm?.querySelector('[name="persons[organization_name]"]');

                        if (input) {
                            input.value = organization.name;
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        }

                        this.organizationSuggestions = [];
                        this.showOrganizationSuggestions = false;
                    },

                    hideOrganizationSuggestions() {
                        setTimeout(() => {
                            this.showOrganizationSuggestions = false;
                        }, 150);
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
