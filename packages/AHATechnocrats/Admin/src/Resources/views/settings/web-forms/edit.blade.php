<x-admin::layouts>
    @php
        $availableCampaigns = $activeCampaigns ?? \AHATechnocrats\WebForm\Helpers\WebFormCampaigns::activeAsOptions();
        $storedCampaignOptions = old('program_options', $webForm->program_options ?? null);

        if (is_string($storedCampaignOptions)) {
            $storedCampaignOptions = json_decode($storedCampaignOptions, true);
        }

        $campaignScope = old('campaign_scope', $webForm->campaign_scope ?? (empty($storedCampaignOptions) ? 'all' : 'selected'));
        $selectedCampaignKeys = $campaignScope === 'selected' && ! empty($storedCampaignOptions)
            ? array_map('strval', $storedCampaignOptions)
            : array_column($availableCampaigns, 'key');
        $allCampaignsSelected = $campaignScope !== 'selected';
    @endphp

    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.webforms.edit.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.web_forms.update', $webForm->id)"
        method="PUT"
    >
        <div class="flex flex-col gap-4">
            <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    {!! view_render_event('admin.settings.webform.edit.breadcrumbs.before', ['webform' => $webForm]) !!}

                    <!-- Breadcurmbs -->
                    <x-admin::breadcrumbs
                        name="web_forms.edit"
                        :entity="$webForm"
                    />

                    {!! view_render_event('admin.settings.webform.edit.breadcrumbs.after', ['webform' => $webForm]) !!}

                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.settings.webforms.edit.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.settings.webform.edit.embed_button.before', ['webform' => $webForm]) !!}

                        <a
                            href="{{ route('admin.web_forms.responses.index', $webForm->id) }}"
                            class="secondary-button"
                        >
                            @lang('admin::app.settings.webforms.responses.title-short')
                        </a>

                        <!-- Edit Button For Person -->
                        <button
                            type="button"
                            class="secondary-button"
                            @click="$refs.webform.openModal()"
                        >
                            @lang('admin::app.settings.webforms.edit.embed')
                        </button>

                        {!! view_render_event('admin.settings.webform.edit.embed_button.after', ['webform' => $webForm]) !!}

                        {!! view_render_event('admin.settings.webform.edit.preview_button.before', ['webform' => $webForm]) !!}

                        <a
                            href="{{ route('admin.settings.web_forms.preview', $webForm->form_id) }}"
                            target="_blank"
                            class="secondary-button"
                        >
                            @lang('admin::app.settings.webforms.edit.preview')
                        </a>

                        {!! view_render_event('admin.settings.webform.edit.preview_button.after', ['webform' => $webForm]) !!}

                        {!! view_render_event('admin.settings.webform.edit.save_button.before', ['webform' => $webForm]) !!}

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('admin::app.settings.webforms.edit.save-btn')
                        </button>

                        {!! view_render_event('admin.settings.webform.edit.save_button.after', ['webform' => $webForm]) !!}
                    </div>
                </div>
            </div>

            <v-webform ref="webform"></v-webform>
        </div>
    </x-admin::form>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-webform-template"
        >
            <div class="flex flex-col gap-2.5">
                {!! view_render_event('admin.settings.webform.edit.left.before', ['webform' => $webForm]) !!}

                <div class="flex w-full flex-col gap-2">
                    <div class="box-shadow rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="mb-4 flex flex-wrap items-center justify-end gap-2 border-b border-gray-200 pb-4 dark:border-gray-800">
                            @include('admin::settings.web-forms.partials.customization-drawer', ['mode' => 'edit', 'webForm' => $webForm])
                        </div>

                        {!! view_render_event('admin.settings.webform.edit.form_controls.before', ['webform' => $webForm]) !!}

                            @include('admin::settings.web-forms.partials.hidden-required-fields')
                            @include('admin::settings.web-forms.partials.form-metadata', ['mode' => 'edit', 'webForm' => $webForm])
                            @include('admin::settings.web-forms.partials.form-behavior', ['mode' => 'edit', 'webForm' => $webForm])

                            <!-- Attributes -->
                            <div class="mb-4 mt-6 flex items-center justify-between gap-4 border-t border-gray-200 pt-6 dark:border-gray-800">
                                <div class="flex flex-col gap-1">
                                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                                        @lang('admin::app.settings.webforms.edit.attributes')
                                    </p>

                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        @lang('admin::app.settings.webforms.edit.attributes-info')
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
                                            @lang('admin::app.settings.webforms.edit.add-attribute-btn')
                                        </button>
                                    </x-slot>

                                    <x-slot:menu class="max-h-80 overflow-y-auto !p-0 dark:border-gray-800">
                                        <template v-if="createLead">
                                            <div class="m-2 text-lg font-bold">
                                                @lang('admin::app.settings.webforms.edit.leads')
                                            </div>

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

                                        <div class="m-2 text-lg font-bold">
                                            @lang('admin::app.settings.webforms.edit.person')
                                        </div>

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
                                                    Built-in field
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
                                                <p class="mt-6 text-sm text-gray-400 dark:text-gray-500">
                                                    —
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
                        </div>

                            {!! view_render_event('admin.settings.webform.edit.form_controls.after', ['webform' => $webForm]) !!}
                        </div>
                    </div>

                {!! view_render_event('admin.settings.webform.edit.left.after', ['webform' => $webForm]) !!}

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
                                    <option value="boolean">Yes/No (Boolean)</option>
                                    <option value="date">Date</option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>

                            <!-- Target Entity -->
                            <x-admin::form.control-group class="mb-4">
                                <x-admin::form.control-group.label class="required">
                                    Save Value Under
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="new_field_entity_type"
                                    v-model="customField.entity_type"
                                    label="Save Value Under"
                                >
                                    <option value="persons">Person (Contact)</option>
                                    <option value="leads" v-if="createLead">Lead</option>
                                    <option value="organizations">Organization</option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>

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

                <x-admin::modal ref="embed">
                    <!-- Modal Header -->
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.webforms.edit.preview')
                        </p>
                    </x-slot>

                    <!-- Modal Content -->
                    <x-slot:content class="!border-b-0">
                        {!! view_render_event('admin.settings.webform.edit.modal.form_controls.before', ['webform' => $webForm]) !!}

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.webforms.edit.public-url')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="publicUrl"
                                name="publicUrl"
                                rules="required"
                                :value="route('admin.settings.web_forms.preview', $webForm->form_id)"
                                :label="trans('admin::app.settings.webforms.edit.public-url')"
                                :placeholder="trans('admin::app.settings.webforms.edit.public-url')"
                            />

                            <span
                                id="publicUrlBtn"
                                class="cursor-pointer text-xs font-normal text-brandColor hover:text-sky-600 hover:underline"
                                @click="copyToClipboard('#publicUrl','#publicUrlBtn')"
                            >
                                @lang('admin::app.settings.webforms.edit.copy')
                            </span>
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.webforms.edit.code-snippet')
                            </x-admin::form.control-group.label>

                            <input
                                type="text"
                                id="codeSnippet"
                                name="codeSnippet"
                                class="w-full rounded border border-gray-300 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                value="{{ '<script src="'.route('admin.settings.web_forms.form_js', $webForm->form_id).'"></script>' }}"
                            />

                            <span
                                id="coeSnippt"
                                class="cursor-pointer text-xs font-normal text-brandColor hover:text-sky-600 hover:underline"
                                @click="copyToClipboard('#codeSnippet','#coeSnippt')"
                            >
                                @lang('admin::app.settings.webforms.edit.copy')
                            </span>
                        </x-admin::form.control-group>

                        {!! view_render_event('admin.settings.webform.edit.modal.form_controls.after', ['webform' => $webForm]) !!}
                    </x-slot>
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
                        submitSuccessAction: {
                            value: '{{ old('submit_success_action', $webForm->submit_success_action) }}',

                            options: [
                                { value: 'message', label: '@lang('admin::app.settings.webforms.edit.display-custom-message')' },
                                { value: 'redirect', label: '@lang('admin::app.settings.webforms.edit.redirect-to-url')' },
                            ],
                        },

                        createLead: {{ old('create_lead', $webForm->create_lead) ? 'true' : 'false' }},

                        organizationField: 'required',

                        programField: 'required',

                        availablePrograms: @json($availableCampaigns),

                        isActive: {{ old('is_active', $webForm->is_active ?? true) ? 'true' : 'false' }},

                        sendSubmitterEmail: {{ old('send_submitter_email', $webForm->send_submitter_email ?? false) ? 'true' : 'false' }},

                        emailTemplateId: '{{ old('email_template_id', $webForm->email_template_id ?? '') }}',

                        campaignScope: '{{ $campaignScope }}',

                        availableCampaigns: @json($availableCampaigns),

                        allCampaignsSelected: {{ $allCampaignsSelected ? 'true' : 'false' }},

                        selectedCampaignKeys: @json($selectedCampaignKeys),

                        allowOrgCreate: {{ (old('allow_org_create') ?? $webForm->allow_org_create) ? 'true' : 'false' }},

                        formTitle: @json(old('title', $webForm->title)),

                        submitButtonLabel: @json(old('submit_button_label', $webForm->submit_button_label ?? 'Submit')),

                        backgroundColor: @json(old('background_color', $webForm->background_color ?? '#F7F8F9')),

                        formBackgroundColor: @json(old('form_background_color', $webForm->form_background_color ?? '#FFFFFF')),

                        formTitleColor: @json(old('form_title_color', $webForm->form_title_color ?? '#263238')),

                        formSubmitButtonColor: @json(old('form_submit_button_color', $webForm->form_submit_button_color ?? '#0E90D9')),

                        attributeLabelColor: @json(old('attribute_label_color', $webForm->attribute_label_color ?? '#546E7A')),

                        customField: {
                            name: '',
                            type: 'text',
                            entity_type: 'persons',
                            is_required: false,
                        },

                        attributes: @json($attributes),

                        formFields: @json(\AHATechnocrats\WebForm\Helpers\WebFormFieldOrder::buildEditorFields($webForm)),

                        attributeCount: {{ $webForm->attributes()->count() }},
                    }
                },

                watch: {
                    createLead(newValue, oldValue) {
                        if (newValue) {
                            return;
                        }

                        this.formFields = this.formFields.filter(field => {
                            return field.type === 'builtin' || field.attribute?.entity_type != 'leads';
                        });

                        this.pinInquiryDetailsLast();
                    },

                    organizationField(newValue) {
                        const orgKey = 'builtin:organization';
                        const orgIndex = this.formFields.findIndex(field => field.key === orgKey);

                        if (newValue !== 'none' && orgIndex === -1) {
                            const emailIndex = this.formFields.findIndex(field => field.key === 'builtin:email');

                            this.formFields.splice(emailIndex + 1, 0, {
                                key: orgKey,
                                type: 'builtin',
                                label: 'Company / Organization / University',
                                locked: false,
                                removable: false,
                            });
                        } else if (newValue === 'none' && orgIndex !== -1) {
                            this.formFields.splice(orgIndex, 1);
                        }

                        this.pinInquiryDetailsLast();
                    },

                    programField(newValue) {
                        const programKey = 'builtin:program';
                        const programIndex = this.formFields.findIndex(field => field.key === programKey);

                        if (newValue !== 'none' && programIndex === -1) {
                            const inquiryIndex = this.formFields.findIndex(field => field.key === 'builtin:inquiry_details');

                            const programField = {
                                key: programKey,
                                type: 'builtin',
                                label: 'Interested in Program',
                                locked: false,
                                removable: false,
                            };

                            if (inquiryIndex === -1) {
                                this.formFields.push(programField);
                            } else {
                                this.formFields.splice(inquiryIndex, 0, programField);
                            }
                        } else if (newValue === 'none' && programIndex !== -1) {
                            this.formFields.splice(programIndex, 1);
                        }

                        this.pinInquiryDetailsLast();
                    },
                },

                computed:{
                    campaignOptionsJson() {
                        if (this.campaignScope !== 'selected' || this.selectedCampaignKeys.length === 0) {
                            return '[]';
                        }

                        if (this.selectedCampaignKeys.length === this.availableCampaigns.length) {
                            return '[]';
                        }

                        return JSON.stringify(this.selectedCampaignKeys);
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
                    /**
                     * Update createLead value from create_lead switch.
                     *
                     * @param {Event} event
                     * @return {void}
                     */
                    onCreateLeadChange(event) {
                        this.createLead = event.target.checked;
                    },

                    openCustomFieldModal() {
                        this.$refs.customFieldModal.toggle();
                    },

                    saveCustomField() {
                        if (! this.customField.name) {
                            alert("Please enter a field label / question");
                            return;
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
                            }
                        });

                        this.customField.name = '';
                        this.customField.is_required = false;

                        this.$refs.customFieldModal.toggle();
                    },

                    /**
                     * Copy the value to the clipboard.
                     *
                     * @param {String} ref
                     * @param {String} btn
                     *
                     * @return {void}
                     */
                    copyToClipboard(ref, btn) {
                        const element = document.querySelector(ref);

                        const btnElement = document.querySelector(btn);

                        element.select();

                        document.execCommand('copy');

                        btnElement.textContent = "@lang('admin::app.settings.webforms.edit.copied')!";

                        setTimeout(() => btnElement.textContent = "Copy", 1000);
                    },

                    /**
                     * Open the modal based on the type.
                     *
                     * @param {String} type
                     *
                     * @return {void}
                     */
                    openModal() {
                        this.$refs.embed.toggle();
                    },

                    /**
                     * Add the attribute to the added attributes list.
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
                            return "@lang('admin::app.settings.webforms.edit.choose-value')";
                        } else if (attribute.type == 'file') {
                            return "@lang('admin::app.settings.webforms.edit.select-file')";
                        } else if (attribute.type == 'image') {
                            return "@lang('admin::app.settings.webforms.edit.select-image')";
                        } else {
                            return "@lang('admin::app.settings.webforms.edit.enter-value')";
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
