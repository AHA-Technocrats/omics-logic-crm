<x-admin::layouts>
    @php
        $availableCampaigns = $activeCampaigns ?? \AHATechnocrats\WebForm\Helpers\WebFormCampaigns::activeAsOptions();
        $storedCampaignOptions = old('program_options', null);

        if (is_string($storedCampaignOptions)) {
            $storedCampaignOptions = json_decode($storedCampaignOptions, true);
        }

        $campaignScope = old('campaign_scope', 'all');
        $selectedCampaignKeys = $campaignScope === 'selected' && ! empty($storedCampaignOptions)
            ? array_map('strval', $storedCampaignOptions)
            : array_column($availableCampaigns, 'key');
        $allCampaignsSelected = $campaignScope !== 'selected';
        $programField = old('program_field', 'required');
        if ($programField === 'optional') {
            $programField = 'required';
        }
        if (! in_array($programField, ['none', 'required'], true)) {
            $programField = 'required';
        }
    @endphp

    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.webforms.create.title')
    </x-slot>

    <x-admin::form :action="route('admin.web_forms.store')">
        <div class="flex flex-col gap-4">
            <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('admin.settings.webform.create.breadcrumbs.before') !!}

                    <!-- Breadcrumbs -->
                    <x-admin::breadcrumbs name="web_forms.create" />

                    {!! view_render_event('admin.settings.webform.create.breadcrumbs.after') !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.settings.webforms.create.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    {!! view_render_event('admin.settings.webform.create.save_button.before') !!}
                    {!! view_render_event('admin.settings.webform.create.save_button.after') !!}
                </div>
            </div>

            <v-webform :errors="errors"></v-webform>
        </div>
    </x-admin::form>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-webform-template"
        >
            <div class="flex flex-col gap-2.5">
                {!! view_render_event('admin.settings.webform.create.left.before') !!}

                <div class="flex w-full flex-col gap-2">
                    <div class="box-shadow rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        @include('admin::settings.web-forms.partials.wizard-timeline', ['mode' => 'create'])

                        {!! view_render_event('admin.settings.webform.create.form_controls.before') !!}

                        @include('admin::settings.web-forms.partials.hidden-required-fields')

                        <div v-show="currentStep === 1" class="wizard-step" data-step="1">
                            @include('admin::settings.web-forms.partials.form-metadata', ['mode' => 'create'])

                            <!-- Attributes -->
                            <div class="mb-4 mt-6 flex items-center justify-between gap-4 border-t border-gray-200 pt-6 dark:border-gray-800">
                                <div class="flex flex-col gap-1">
                                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                                        @lang('admin::app.settings.webforms.create.attributes')
                                    </p>

                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        @lang('admin::app.settings.webforms.create.attributes-info')
                                    </p>
                                </div>
                            </div>

                        <div class="flex flex-col gap-4">
                            <div class="flex gap-2">
                                <x-admin::dropdown class="rounded-lg group-hover:visible dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400">
                                    <x-slot:toggle>
                                        <button
                                            type="button"
                                            class="primary-button"
                                        >
                                            @lang('admin::app.settings.webforms.create.add-attribute-btn')
                                        </button>
                                    </x-slot>

                                    <x-slot:menu class="max-h-80 overflow-y-auto !p-0 dark:border-gray-800">
                                        <template v-if="createLead">
                                            <div class="m-2 text-lg font-bold">@lang('admin::app.settings.webforms.create.leads')</div>

                                            <span
                                                v-for="attribute in groupedAttributes.leads"
                                                class="whitespace-no-wrap flex cursor-pointer items-center justify-between gap-1.5 rounded-t px-2 py-2 text-sm text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-950"
                                                @click="addAttribute(attribute)"
                                            >
                                                <div class="items flex items-center gap-1.5">
                                                    @{{ attribute.name }}
                                                </div>
                                            </span>
                                        </template>

                                        <div class="m-2 text-lg font-bold">@lang('admin::app.settings.webforms.create.person')</div>

                                        <span
                                            v-for="attribute in groupedAttributes.persons"
                                            class="whitespace-no-wrap flex cursor-pointer items-center justify-between gap-1.5 rounded-t px-2 py-2 text-sm text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-950"
                                            @click="addAttribute(attribute)"
                                        >
                                            <div class="items flex items-center gap-1.5">
                                                @{{ attribute.name }}
                                            </div>
                                        </span>

                                        <div class="m-2 text-lg font-bold">Organizations</div>

                                        <span
                                            v-for="attribute in groupedAttributes.organizations"
                                            class="whitespace-no-wrap flex cursor-pointer items-center justify-between gap-1.5 rounded-t px-2 py-2 text-sm text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-950"
                                            @click="addAttribute(attribute)"
                                        >
                                            <div class="items flex items-center gap-1.5">
                                                @{{ attribute.name }}
                                            </div>
                                        </span>
                                    </x-slot>
                                </x-admin::dropdown>

                                <button
                                    type="button"
                                    class="secondary-button"
                                    @click="openCustomFieldModal"
                                >
                                    + Add Custom Question
                                </button>
                            </div>

                            <input
                                type="hidden"
                                name="field_order"
                                :value="JSON.stringify(formFields.map(field => field.key))"
                            />

                            <!-- Form Fields -->
                            <div class="overflow-x-auto">
                            <table class="w-full">
                            <draggable
                                tag="tbody"
                                ghost-class="draggable-ghost"
                                handle=".icon-move"
                                v-bind="{animation: 200}"
                                item-key="key"
                                :list="formFields"
                                @change="onFieldReorder"
                            >
                                <template #item="{ element, index }">
                                    <x-admin::table.thead.tr class="!rounded-lg hover:bg-gray-50 dark:hover:bg-gray-950">
                                        <!-- Draggable Icon -->
                                        <x-admin::table.td class="text-center">
                                            <i class="icon-move cursor-grab rounded-md text-2xl transition-all hover:bg-gray-100 dark:hover:bg-gray-950"></i>

                                            <template v-if="element.type === 'attribute'">
                                                <template v-if="element.is_new">
                                                    <input
                                                        type="hidden"
                                                        :value="1"
                                                        :name="'attributes[' + element.id + '][is_new]'"
                                                    />
                                                    <input
                                                        type="hidden"
                                                        :value="element.attribute.code"
                                                        :name="'attributes[' + element.id + '][code]'"
                                                    />
                                                    <input
                                                        type="hidden"
                                                        :value="element.attribute.type"
                                                        :name="'attributes[' + element.id + '][type]'"
                                                    />
                                                    <input
                                                        type="hidden"
                                                        :value="element.attribute.entity_type"
                                                        :name="'attributes[' + element.id + '][entity_type]'"
                                                    />
                                                    <template v-if="element.attribute.options?.length">
                                                        <input
                                                            v-for="(option, optionIndex) in element.attribute.options"
                                                            :key="optionIndex"
                                                            type="hidden"
                                                            :value="option.name"
                                                            :name="'attributes[' + element.id + '][options][' + optionIndex + '][name]'"
                                                        />
                                                    </template>
                                                </template>
                                                <template v-else>
                                                    <input
                                                        type="hidden"
                                                        :value="element.id"
                                                        :name="'attributes[' + element.id + '][id]'"
                                                    />
                                                    <input
                                                        type="hidden"
                                                        :value="element['attribute']['id']"
                                                        :name="'attributes[' + element.id + '][attribute_id]'"
                                                    />
                                                </template>

                                                <input
                                                    type="hidden"
                                                    :value="index"
                                                    :name="'attributes[' + element.id + '][sort_order]'"
                                                />
                                            </template>
                                        </x-admin::table.td>

                                        <!-- Field Name -->
                                        <x-admin::table.td>
                                            <template v-if="element.type === 'builtin'">
                                                <p class="text-sm font-medium text-gray-800 dark:text-white">
                                                    @{{ element.label }}
                                                </p>

                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    <template v-if="element.key === 'builtin:program'">
                                                        Required
                                                    </template>
                                                    <template v-else>
                                                        Built-in field
                                                    </template>
                                                </p>
                                            </template>

                                            <template v-else>
                                                <x-admin::form.control-group>
                                                    <x-admin::form.control-group.label class="">
                                                        @{{ element?.name + ' (' + element?.attribute?.entity_type + ')' }}
                                                    </x-admin::form.control-group.label>

                                                    <x-admin::form.control-group.control
                                                        type="text"
                                                        ::name="'attributes[' + element.id + '][name]'"
                                                        v-model="element.name"
                                                    />

                                                    <x-admin::form.control-group.error ::name="'attributes[' + element.id + '][name]'"/>
                                                </x-admin::form.control-group>
                                            </template>
                                        </x-admin::table.td>

                                        <!-- Placeholder -->
                                        <x-admin::table.td>
                                            <template v-if="element.type === 'builtin'">
                                                <p class="mt-6 text-sm text-gray-400 dark:text-gray-500">
                                                    —
                                                </p>
                                            </template>

                                            <template v-else>
                                                <x-admin::form.control-group class="!mt-6">
                                                    <x-admin::form.control-group.control
                                                        type="text"
                                                        ::name="'attributes[' + element.id + '][placeholder]'"
                                                        ::rules="element.attribute.validation"
                                                        ::label="element?.name + ' (' + element?.attribute?.entity_type + ')'"
                                                        ::placeholder="getPlaceholderValue(element)"
                                                        v-model="element.placeholder"
                                                    />

                                                    <x-admin::form.control-group.error ::name="'attributes[' + element.id + '][placeholder]'"/>
                                                </x-admin::form.control-group>
                                            </template>
                                        </x-admin::table.td>

                                        <!-- Required Or Not -->
                                        <x-admin::table.td>
                                            <template v-if="element.type === 'builtin'">
                                                <p
                                                    class="mt-6 text-sm"
                                                    :class="element.key === 'builtin:program'
                                                        ? 'font-medium text-gray-800 dark:text-white'
                                                        : 'text-gray-400 dark:text-gray-500'"
                                                >
                                                    <template v-if="element.key === 'builtin:program'">
                                                        Required
                                                    </template>
                                                    <template v-else>
                                                        —
                                                    </template>
                                                </p>
                                            </template>

                                            <template v-else>
                                                <x-admin::form.control-group class="!mt-6">
                                                    <label :for="'attributes[' + element.id + '][is_required]'">
                                                        <input
                                                            type="hidden"
                                                            :name="'attributes[' + element.id + '][is_required]'"
                                                            :value="1"
                                                            v-if="['name', 'emails'].includes(element['attribute']['code'])"
                                                        >

                                                        <input
                                                            type="checkbox"
                                                            :name="'attributes[' + element.id + '][is_required]'"
                                                            :id="'attributes[' + element.id + '][is_required]'"
                                                            :value="1"
                                                            class="peer hidden"
                                                            :checked="element.is_required"
                                                            :disabled="['name', 'emails'].includes(element['attribute']['code'])"
                                                        >

                                                        <span
                                                            class='icon-checkbox-outline peer-checked:icon-checkbox-select cursor-pointer rounded-md text-2xl peer-checked:text-brandColor'
                                                            :class="{'opacity-50' : ['name', 'emails'].includes(element['attribute']['code'])}"
                                                        ></span>
                                                    </label>
                                                </x-admin::form.control-group>
                                            </template>
                                        </x-admin::table.td>

                                        <!-- Actions -->
                                        <x-admin::table.td>
                                            <x-admin::form.control-group class="!mt-6">
                                                <i
                                                    class="icon-delete cursor-pointer text-2xl"
                                                    v-if="element.type === 'attribute' && ! ['name', 'emails'].includes(element['attribute']['code'])"
                                                    @click="removeAttribute(element)"
                                                ></i>
                                            </x-admin::form.control-group>
                                        </x-admin::table.td>
                                    </x-admin::table.thead.tr>
                                </template>
                            </draggable>
                            </table>
                            </div>
                        </div>
                        </div>

                        <div v-show="currentStep === 2" class="wizard-step" data-step="2">
                            @include('admin::settings.web-forms.partials.lead-email', ['mode' => 'create'])
                        </div>

                        <div v-show="currentStep === 3" class="wizard-step" data-step="3">
                            @include('admin::settings.web-forms.partials.form-behavior', ['mode' => 'create'])
                        </div>

                        <div v-show="currentStep === 4" class="wizard-step" data-step="4">
                            @include('admin::settings.web-forms.partials.customization-panel', ['mode' => 'create'])
                        </div>

                            {!! view_render_event('admin.settings.webform.create.form_controls.after') !!}

                        @include('admin::settings.web-forms.partials.wizard-nav', ['mode' => 'create'])
                        </div>
                    </div>

                {!! view_render_event('admin.settings.webform.create.left.after') !!}

                <x-admin::modal ref="customFieldModal">
                    <!-- Modal Header -->
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            Add Custom Question (Google Forms Style)
                        </p>
                    </x-slot:header>

                    <!-- Modal Content -->
                    <x-slot:content>
                        <div class="flex flex-col gap-4">
                            <!-- Field Label -->
                            <x-admin::form.control-group class="mb-4">
                                <x-admin::form.control-group.label class="required">
                                    Question / Field Label
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="new_field_name"
                                    v-model="customField.name"
                                    label="Question / Field Label"
                                    placeholder="e.g. What is your primary interest?"
                                />
                            </x-admin::form.control-group>

                            <!-- Field Type -->
                            <x-admin::form.control-group class="mb-4">
                                <x-admin::form.control-group.label class="required">
                                    Field Type
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="new_field_type"
                                    v-model="customField.type"
                                    label="Field Type"
                                >
                                    <option value="text">Short Answer (Text)</option>
                                    <option value="textarea">Paragraph (Textarea)</option>
                                    <option value="checkbox">Checkboxes (Multiple choice)</option>
                                    <option value="boolean">Yes/No (Boolean)</option>
                                    <option value="date">Date</option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>

                            <div
                                v-if="customField.type === 'checkbox'"
                                class="mb-4 rounded-lg border border-gray-200 p-3 dark:border-gray-800"
                            >
                                <p class="mb-2 text-sm font-semibold text-gray-800 dark:text-white">
                                    Checkbox options
                                </p>

                                <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">
                                    Add the choices visitors can select (multiple allowed).
                                </p>

                                <div class="mb-2 flex flex-col gap-2">
                                    <div
                                        v-for="(option, index) in customField.options"
                                        :key="index"
                                        class="flex items-center gap-2"
                                    >
                                        <x-admin::form.control-group.control
                                            type="text"
                                            ::name="'custom_option_' + index"
                                            v-model="customField.options[index]"
                                            label="Option"
                                            placeholder="Option label"
                                            class="flex-1"
                                        />

                                        <button
                                            type="button"
                                            class="secondary-button !px-2"
                                            @click="removeCustomFieldOption(index)"
                                            v-if="customField.options.length > 1"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="secondary-button"
                                    @click="addCustomFieldOption"
                                >
                                    + Add Option
                                </button>
                            </div>

                            <!-- Required -->
                            <x-admin::form.control-group class="mb-4">
                                <x-admin::form.control-group.label for="new_field_required">
                                    Required Question
                                </x-admin::form.control-group.label>

                                <input
                                    type="hidden"
                                    name="new_field_required"
                                    :value="0"
                                />

                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="new_field_required"
                                    value="1"
                                    :checked="false"
                                    v-model="customField.is_required"
                                />
                            </x-admin::form.control-group>

                            <!-- Action Buttons -->
                            <div class="flex justify-end gap-2 mt-4">
                                <button type="button" class="secondary-button" @click="$refs.customFieldModal.toggle()">
                                    Cancel
                                </button>
                                <button type="button" class="primary-button" @click="saveCustomField">
                                    Add Question
                                </button>
                            </div>
                        </div>
                    </x-slot:content>
                </x-admin::modal>

            </div>
        </script>

        <script
            type="text/x-template"
            id="v-color-picker-template"
        >
            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @{{ title }}
                </x-admin::form.control-group.label>

                <div class="flex">
                    <x-admin::form.control-group.control
                        type="text"
                        ::name="name"
                        ::id="name"
                        class="rounded-r-none"
                        rules="required"
                        ::label="title"
                        v-model="color"
                    />

                    <x-admin::form.control-group.control
                        type="color"
                        class="!h-10 !w-12 rounded-l-none p-1 dark:border-gray-800 dark:bg-gray-900"
                        name="color"
                        :label="trans('Submit Success Action')"
                        ::value="color"
                        @input="color = $event.target.value"
                    />
                </div>

                <x-admin::form.control-group.error ::name="name"/>
            </x-admin::form.control-group>
        </script>

        <script type="module">
            app.component('v-webform', {
                template: '#v-webform-template',

                data() {
                    return {
                        currentStep: 1,

                        wizardSteps: [
                            { id: 1, label: '@lang('admin::app.settings.webforms.create.step-form')' },
                            { id: 2, label: '@lang('admin::app.settings.webforms.create.step-lead-email')' },
                            { id: 3, label: '@lang('admin::app.settings.webforms.create.step-after-submit')' },
                            { id: 4, label: '@lang('admin::app.settings.webforms.create.step-customization')' },
                        ],

                        submitSuccessAction: {
                            value: '{{ old('submit_success_action', 'message') }}',

                            options: [
                                { value: 'message', label: '@lang('admin::app.settings.webforms.create.display-custom-message')' },
                                { value: 'redirect', label: '@lang('admin::app.settings.webforms.create.redirect-to-url')' },
                            ],
                        },

                        createLead: {{ old('create_lead') ? 'true' : 'false' }},

                        availablePrograms: @json($availableCampaigns),

                        customField: {
                            name: '',
                            type: 'text',
                            entity_type: 'webforms',
                            is_required: false,
                            options: ['', ''],
                        },

                        attributes: @json($attributes['other']),

                        formFields: @json($defaultFields),

                        attributeCount: 0,

                        isActive: {{ old('is_active', true) ? 'true' : 'false' }},

                        sendSubmitterEmail: {{ old('send_submitter_email') ? 'true' : 'false' }},

                        emailTemplateId: '{{ old('email_template_id', '') }}',

                        organizationField: 'required',

                        programField: @json($programField),

                        isApplyingCustomization: false,

                        customizationSaveUrl: null,

                        campaignScope: '{{ $campaignScope }}',

                        availableCampaigns: @json($availableCampaigns),

                        allCampaignsSelected: {{ $allCampaignsSelected ? 'true' : 'false' }},

                        selectedCampaignKeys: @json($selectedCampaignKeys),

                        allowOrgCreate: {{ old('allow_org_create', true) ? 'true' : 'false' }},

                        showCampaignOther: {{ old('show_campaign_other', true) ? 'true' : 'false' }},

                        formTitle: @json(old('title', '')),

                        submitButtonLabel: @json(old('submit_button_label', 'Submit')),

                        backgroundColor: @json(old('background_color', '#F7F8F9')),

                        formBackgroundColor: @json(old('form_background_color', '#FFFFFF')),

                        formTitleColor: @json(old('form_title_color', '#263238')),

                        formSubmitButtonColor: @json(old('form_submit_button_color', '#0E90D9')),

                        attributeLabelColor: @json(old('attribute_label_color', '#546E7A')),
                    }
                },

                props: ['errors'],

                watch: {
                    errors: {
                        handler(newErrors) {
                            if (newErrors && Object.keys(newErrors).length > 0) {
                                this.jumpToErrorStep(newErrors);
                            }
                        },
                        deep: true,
                    },

                    createLead(newValue) {
                        if (newValue) {
                            return;
                        }

                        this.formFields = this.formFields.filter(field => {
                            return field.type === 'builtin' || field.attribute?.entity_type != 'leads';
                        });

                        this.pinInquiryDetailsLast();
                    },

                    programField(newValue) {
                        this.syncProgramField();
                    },
                },

                computed:{
                    campaignOptionsJson() {
                        if (this.campaignScope !== 'selected') {
                            return '[]';
                        }

                        return JSON.stringify(
                            (this.selectedCampaignKeys || []).map(key => String(key))
                        );
                    },

                    programOptionsJson() {
                        return this.campaignOptionsJson;
                    },

                    /**
                     * Get the placeholder value based on the submit success action value.
                     *
                     * @return {String}
                     */
                    placeholder() {
                        return this.submitSuccessAction.value === 'message' ? '@lang('Enter message to display')' : '@lang('Enter url to redirect')';
                    },

                    /**
                     * Get the grouped attributes based on the entity type.
                     *
                     * @return {Object}
                     */
                    groupedAttributes() {
                        return this.attributes.reduce((r, a) => {
                            r[a.entity_type] = [...r[a.entity_type] || [], a];
                            return r;
                        }, {});
                    },
                },

                methods: {
                    jumpToErrorStep(errors) {
                        const errorKeys = Object.keys(errors);
                        if (errorKeys.length === 0) return;

                        setTimeout(() => {
                            let firstErrorElement = document.querySelector('[name="' + errorKeys[0] + '"]');
                            if (firstErrorElement) {
                                let stepDiv = firstErrorElement.closest('.wizard-step');
                                if (stepDiv) {
                                    let step = parseInt(stepDiv.getAttribute('data-step'));
                                    if (step && this.currentStep !== step) {
                                        this.currentStep = step;
                                        this.refreshWizardEditors();
                                    }
                                }
                            }
                        }, 50);
                    },

                    goToStep(step) {
                        if (step >= 1 && step <= this.wizardSteps.length) {
                            this.currentStep = step;
                            this.refreshWizardEditors();
                        }
                    },

                    nextStep() {
                        if (this.currentStep < this.wizardSteps.length) {
                            this.currentStep += 1;
                            this.refreshWizardEditors();
                        }
                    },

                    prevStep() {
                        if (this.currentStep > 1) {
                            this.currentStep -= 1;
                            this.refreshWizardEditors();
                        }
                    },

                    refreshWizardEditors() {
                        this.$nextTick(() => {
                            if (typeof tinymce === 'undefined') {
                                return;
                            }

                            const editorIds = this.currentStep === 1
                                ? ['description']
                                : (this.currentStep === 3 ? ['thank_you_content'] : []);

                            editorIds.forEach((id) => {
                                const editor = tinymce.get(id);

                                if (! editor) {
                                    return;
                                }

                                editor.show();
                                editor.fire('ResizeEditor');

                                if (editor.getContainer()) {
                                    editor.getContainer().style.display = '';
                                }
                            });
                        });
                    },

                    /**
                     * Update createLead value from create_lead switch.
                     *
                     * @param {Event} event
                     * @return {void}
                     */
                    onCreateLeadChange(event) {
                        this.createLead = event.target.checked;
                    },

                    applyCustomization() {
                        this.syncProgramField();

                        if (! this.customizationSaveUrl) {
                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: "@lang('admin::app.settings.webforms.form.customization-applied')",
                            });

                            return;
                        }

                        this.isApplyingCustomization = true;

                        this.$axios.put(this.customizationSaveUrl, {
                            background_color: this.backgroundColor,
                            form_background_color: this.formBackgroundColor,
                            form_title_color: this.formTitleColor,
                            form_submit_button_color: this.formSubmitButtonColor,
                            attribute_label_color: this.attributeLabelColor,
                            program_field: this.programField === 'required' ? 'required' : 'none',
                            campaign_scope: this.programField === 'required' ? this.campaignScope : 'all',
                            program_options: this.programField === 'required' ? this.campaignOptionsJson : '[]',
                            show_campaign_other: this.programField === 'required' && this.showCampaignOther ? 1 : 0,
                            allow_org_create: this.allowOrgCreate ? 1 : 0,
                            field_order: JSON.stringify(this.formFields.map(field => field.key)),
                        })
                            .then(response => {
                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data.message,
                                });
                            })
                            .catch(error => {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message
                                        || error.response?.data?.errors?.program_field?.[0]
                                        || 'Failed to save customization',
                                });
                            })
                            .finally(() => {
                                this.isApplyingCustomization = false;
                            });
                    },

                    onShowCampaignInterestChange(checked) {
                        this.programField = checked ? 'required' : 'none';
                        this.syncProgramField();
                    },

                    syncProgramField() {
                        this.programField = this.programField === 'required' ? 'required' : 'none';

                        const programKey = 'builtin:program';
                        const programIndex = this.formFields.findIndex(field => field.key === programKey);

                        if (this.programField === 'required' && programIndex === -1) {
                            const inquiryIndex = this.formFields.findIndex(field => field.key === 'builtin:inquiry_details');

                            const programField = {
                                key: programKey,
                                type: 'builtin',
                                label: 'Interested in Campaign',
                                locked: false,
                                removable: false,
                            };

                            if (inquiryIndex === -1) {
                                this.formFields.push(programField);
                            } else {
                                this.formFields.splice(inquiryIndex, 0, programField);
                            }
                        } else if (this.programField === 'none' && programIndex !== -1) {
                            this.formFields.splice(programIndex, 1);
                        }

                        this.pinInquiryDetailsLast();
                    },

                    openCustomFieldModal() {
                        this.customField = {
                            name: '',
                            type: 'text',
                            entity_type: 'webforms',
                            is_required: false,
                            options: ['', ''],
                        };

                        this.$refs.customFieldModal.toggle();
                    },

                    addCustomFieldOption() {
                        this.customField.options.push('');
                    },

                    removeCustomFieldOption(index) {
                        if (this.customField.options.length <= 1) {
                            return;
                        }

                        this.customField.options.splice(index, 1);
                    },

                    saveCustomField() {
                        if (! this.customField.name) {
                            alert("Please enter a field label / question");
                            return;
                        }

                        let options = [];

                        if (this.customField.type === 'checkbox') {
                            options = (this.customField.options || [])
                                .map(option => String(option || '').trim())
                                .filter(option => option !== '')
                                .map(name => ({ name }));

                            if (options.length < 2) {
                                alert("Please add at least two checkbox options");
                                return;
                            }
                        }

                        let code = 'custom_' + this.customField.name.toLowerCase().replace(/[^a-z0-9]/g, '_') + '_' + Math.floor(Math.random() * 100000);
                        let tempId = 'attribute_' + this.attributeCount++;

                        this.insertFieldBeforeInquiry({
                            key: 'attribute:' + tempId,
                            type: 'attribute',
                            id: tempId,
                            name: this.customField.name,
                            is_required: this.customField.is_required,
                            is_new: true,
                            attribute: {
                                id: null,
                                code: code,
                                name: this.customField.name,
                                type: this.customField.type,
                                entity_type: this.customField.entity_type,
                                is_required: this.customField.is_required,
                                options: options,
                            }
                        });

                        this.customField = {
                            name: '',
                            type: 'text',
                            entity_type: 'webforms',
                            is_required: false,
                            options: ['', ''],
                        };

                        this.$refs.customFieldModal.toggle();
                    },

                    /**
                     * Add the attribute to the form fields list.
                     *
                     * @param {Object} attribute
                     *
                     * @return {void}
                     */
                    addAttribute(attribute) {
                        let tempId = 'attribute_' + this.attributeCount++;

                        this.insertFieldBeforeInquiry({
                            key: 'attribute:' + tempId,
                            type: 'attribute',
                            id: tempId,
                            name: attribute.name,
                            is_required: attribute.is_required,
                            attribute: attribute,
                        });

                        const index = this.attributes.indexOf(attribute);

                        if (index > -1) {
                            this.attributes.splice(index, 1);
                        }
                    },

                    /**
                     * Remove the attribute from the form fields list.
                     *
                     * @param {Object} attribute
                     *
                     * @return {void}
                     */
                    removeAttribute(attribute) {
                        this.attributes.push(attribute.attribute);

                        const index = this.formFields.findIndex(field => field.key === attribute.key);

                        if (index > -1) {
                            this.formFields.splice(index, 1);
                        }

                        this.pinInquiryDetailsLast();
                    },

                    insertFieldBeforeInquiry(field) {
                        const inquiryIndex = this.formFields.findIndex(fieldItem => fieldItem.key === 'builtin:inquiry_details');

                        if (inquiryIndex === -1) {
                            this.formFields.push(field);
                        } else {
                            this.formFields.splice(inquiryIndex, 0, field);
                        }
                    },

                    onFieldReorder() {
                        this.pinInquiryDetailsLast();
                    },

                    pinInquiryDetailsLast() {
                        const inquiryKey = 'builtin:inquiry_details';
                        const inquiryIndex = this.formFields.findIndex(field => field.key === inquiryKey);

                        if (inquiryIndex === -1) {
                            return;
                        }

                        if (inquiryIndex !== this.formFields.length - 1) {
                            const [inquiryField] = this.formFields.splice(inquiryIndex, 1);
                            this.formFields.push(inquiryField);
                        }
                    },

                    /**
                     * Get the placeholder value based on the attribute type.
                     *
                     * @param {Object} attribute
                     *
                     * @return {String}
                     */
                    getPlaceholderValue(attribute) {
                        if (attribute.type == 'select'
                            || attribute.type == 'multiselect'
                            || attribute.type == 'checkbox'
                            || attribute.type == 'boolean'
                            || attribute.type == 'lookup'
                            || attribute.type == 'datetime'
                            || attribute.type == 'date'
                        ) {
                            return "@lang('admin::app.settings.webforms.create.choose-value')";
                        } else if (attribute.type == 'file') {
                            return "@lang('admin::app.settings.webforms.create.select-file')";
                        } else if (attribute.type == 'image') {
                            return "@lang('admin::app.settings.webforms.create.select-image')";
                        } else {
                            return "@lang('admin::app.settings.webforms.create.enter-value')";
                        }
                    },
                },
            });
        </script>

        <script type="module">
            app.component('v-color-picker', {
                template: '#v-color-picker-template',

                props: {
                    name: {
                        type: String,
                        required: true,
                    },

                    value: {
                        type: String,
                        default: '#ffffff',
                    },

                    modelValue: {
                        type: String,
                        default: '',
                    },

                    title: {
                        type: String,
                        required: true,
                    },
                },

                emits: ['update:modelValue'],

                data() {
                    return {
                        color: this.modelValue || this.value,
                    };
                },

                watch: {
                    color(newValue) {
                        this.$emit('update:modelValue', newValue);
                    },

                    modelValue(newValue) {
                        if (newValue !== this.color) {
                            this.color = newValue;
                        }
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
