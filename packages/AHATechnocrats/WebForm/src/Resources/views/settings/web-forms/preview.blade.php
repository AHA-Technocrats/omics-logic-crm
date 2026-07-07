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
                                @include('web_form::settings.web-forms.controls')

                                <input type="hidden" name="_form_token" value="{{ $formToken ?? '' }}" />

                                <input
                                    type="text"
                                    name="{{ config('omicslogic.anti_spam.honeypot_field', '_website_url') }}"
                                    tabindex="-1"
                                    autocomplete="off"
                                    style="position:absolute;left:-9999px;height:0;width:0;opacity:0"
                                />

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
                    };
                },

                methods: {
                    organizationSearchUrl() {
                        return '{{ route('admin.settings.web_forms.organizations.search') }}';
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

                    create(params, { resetForm, setErrors }) {
                        this.isStoring = true;

                        const formData = new FormData(this.$refs.webForm);

                        let inputNames = Array.from(formData.keys());

                        inputNames = inputNames.reduce((acc, name) => {
                            const dotName = name.replace(/\[([^\]]+)\]/g, '.$1');

                            acc[dotName] = name;

                            return acc;
                        }, {});

                        this.$axios
                            .post('{{ route('admin.settings.web_forms.form_store', $webForm->id) }}', formData, {
                                headers: {
                                    'Content-Type': 'multipart/form-data',
                                },
                            })
                            .then(response => {
                                resetForm();

                                this.$refs.webForm.reset();

                                this.programInterest = '';
                                this.selectedOrganizationId = '';
                                this.organizationSuggestions = [];
                                this.showOrganizationSuggestions = false;

                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                            })
                            .catch(error => {
                                if (error.response.data.redirect) {
                                    window.location.href = error.response.data.redirect;

                                    return;
                                }

                                if (! error.response.data.errors) {
                                    this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });

                                    return;
                                }

                                const laravelErrors = error.response.data.errors || {};
                                const mappedErrors = {};

                                for (
                                    const [dotKey, messages]
                                    of Object.entries(laravelErrors)
                                ) {
                                    const inputName = inputNames[dotKey];

                                    if (
                                        inputName
                                        && messages.length
                                    ) {
                                        mappedErrors[inputName] = messages[0];
                                    }
                                }

                                setErrors(mappedErrors);
                            })
                            .finally(() => {
                                this.isStoring = false;
                            });
                    }
                }
            });
        </script>
    @endPushOnce
</x-web_form::layouts>
