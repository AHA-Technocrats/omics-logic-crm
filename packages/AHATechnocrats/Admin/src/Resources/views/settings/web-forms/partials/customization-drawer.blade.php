@php
    $mode = $mode ?? 'create';
    $webForm = $webForm ?? null;
    $langPrefix = $mode === 'edit' ? 'admin::app.settings.webforms.edit' : 'admin::app.settings.webforms.create';
    $defaults = [
        'background_color' => old('background_color', $webForm?->background_color ?? '#F7F8F9'),
        'form_background_color' => old('form_background_color', $webForm?->form_background_color ?? '#FFFFFF'),
        'form_title_color' => old('form_title_color', $webForm?->form_title_color ?? '#263238'),
        'form_submit_button_color' => old('form_submit_button_color', $webForm?->form_submit_button_color ?? '#0E90D9'),
        'attribute_label_color' => old('attribute_label_color', $webForm?->attribute_label_color ?? '#546E7A'),
    ];
@endphp

<x-admin::drawer
    width="380px"
    ref="customizationDrawer"
>
    <x-slot:toggle>
        <button type="button" class="secondary-button">
            <span class="icon-setting text-lg ltr:mr-1.5 rtl:ml-1.5"></span>
            @lang('admin::app.settings.webforms.form.customization')
        </button>
    </x-slot>

    <x-slot:header class="border-b p-3.5 dark:border-gray-800">
        <p class="text-xl font-semibold dark:text-white">
            @lang('admin::app.settings.webforms.form.customization')
        </p>

        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            @lang($langPrefix.'.customize-webform-info')
        </p>
    </x-slot>

    <x-slot:content class="p-4">
        <div class="flex flex-col gap-4">
            <v-color-picker
                name="background_color"
                title="@lang($langPrefix.'.background-color')"
                value="{{ $defaults['background_color'] }}"
                v-model="backgroundColor"
            >
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang($langPrefix.'.background-color')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control type="text" name="background_color" id="background_color" />
                </x-admin::form.control-group>
            </v-color-picker>

            <v-color-picker
                name="form_background_color"
                title="@lang($langPrefix.'.form-background-color')"
                value="{{ $defaults['form_background_color'] }}"
                v-model="formBackgroundColor"
            >
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang($langPrefix.'.form-background-color')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control type="text" name="form_background_color" id="form_background_color" />
                </x-admin::form.control-group>
            </v-color-picker>

            <v-color-picker
                name="form_title_color"
                title="@lang($langPrefix.'.form-title-color')"
                value="{{ $defaults['form_title_color'] }}"
                v-model="formTitleColor"
            >
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang($langPrefix.'.form-title-color')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control type="text" name="form_title_color" id="form_title_color" />
                </x-admin::form.control-group>
            </v-color-picker>

            <v-color-picker
                name="form_submit_button_color"
                title="@lang($langPrefix.'.form-submit-button-color')"
                value="{{ $defaults['form_submit_button_color'] }}"
                v-model="formSubmitButtonColor"
            >
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang($langPrefix.'.form-submit-button-color')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control type="text" name="form_submit_button_color" id="form_submit_button_color" />
                </x-admin::form.control-group>
            </v-color-picker>

            <v-color-picker
                name="attribute_label_color"
                title="@lang($langPrefix.'.attribute-label-color')"
                value="{{ $defaults['attribute_label_color'] }}"
                v-model="attributeLabelColor"
            >
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang($langPrefix.'.attribute-label-color')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control type="text" name="attribute_label_color" id="attribute_label_color" />
                </x-admin::form.control-group>
            </v-color-picker>

            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                <p class="mb-3 text-sm font-semibold text-gray-800 dark:text-white">
                    @lang('admin::app.settings.webforms.form.interested-in-campaign')
                </p>

                <x-admin::form.control-group class="!mb-3">
                    <x-admin::form.control-group.label for="show_campaign_interest">
                        @lang('admin::app.settings.webforms.form.interested-in-campaign-field')
                    </x-admin::form.control-group.label>

                    <input type="hidden" name="program_field" :value="programField" />

                    <label class="flex cursor-pointer items-center gap-2 text-sm dark:text-gray-300">
                        <input
                            id="show_campaign_interest"
                            type="checkbox"
                            class="peer hidden"
                            :checked="programField === 'required'"
                            @change="onShowCampaignInterestChange($event.target.checked)"
                        />
                        <span class="icon-checkbox-outline peer-checked:icon-checkbox-select text-2xl peer-checked:text-brandColor"></span>
                        @lang('admin::app.settings.webforms.form.field-show')
                    </label>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        When shown, this field is always required. Uncheck to hide it from the form.
                    </p>
                </x-admin::form.control-group>

                <div v-show="programField === 'required'">
                    <p class="mb-3 text-sm font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.webforms.form.campaigns-on-form')
                    </p>

                    <input type="hidden" name="campaign_scope" :value="campaignScope" />

                    <x-admin::form.control-group class="!mb-3">
                        <label class="mb-2 flex cursor-pointer items-center gap-2 text-sm dark:text-gray-300">
                            <input type="radio" value="all" v-model="campaignScope" class="peer hidden" />
                            <span class="icon-radio-normal peer-checked:icon-radio-selected text-2xl text-gray-400 peer-checked:text-brandColor"></span>
                            @lang('admin::app.settings.webforms.form.all-campaigns')
                        </label>

                        <label class="flex cursor-pointer items-center gap-2 text-sm dark:text-gray-300">
                            <input type="radio" value="selected" v-model="campaignScope" class="peer hidden" />
                            <span class="icon-radio-normal peer-checked:icon-radio-selected text-2xl text-gray-400 peer-checked:text-brandColor"></span>
                            @lang('admin::app.settings.webforms.form.selected-campaigns')
                        </label>
                    </x-admin::form.control-group>

                    <div v-if="campaignScope === 'selected'" class="max-h-48 space-y-2 overflow-y-auto">
                        <label
                            v-for="campaign in availableCampaigns"
                            :key="campaign.key"
                            class="flex cursor-pointer items-center gap-2 text-sm dark:text-gray-300"
                        >
                            <input
                                type="checkbox"
                                class="peer hidden"
                                :value="String(campaign.key)"
                                v-model="selectedCampaignKeys"
                            />
                            <span class="icon-checkbox-outline peer-checked:icon-checkbox-select text-2xl peer-checked:text-brandColor"></span>
                            @{{ campaign.name }}
                        </label>
                    </div>

                    <input type="hidden" name="program_options" :value="campaignOptionsJson" />
                </div>

                <template v-if="programField === 'none'">
                    <input type="hidden" name="campaign_scope" value="all" />
                    <input type="hidden" name="program_options" value="[]" />
                </template>
            </div>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label for="allow_org_create">
                    @lang('admin::app.settings.webforms.form.allow-org-create')
                </x-admin::form.control-group.label>

                <input type="hidden" name="allow_org_create" :value="0" />

                <x-admin::form.control-group.control
                    type="switch"
                    name="allow_org_create"
                    value="1"
                    :checked="(bool) old('allow_org_create', $webForm?->allow_org_create ?? true)"
                    v-model="allowOrgCreate"
                />
            </x-admin::form.control-group>
        </div>
    </x-slot>

    <x-slot:footer class="flex gap-2.5 p-4">
        <button
            type="button"
            class="primary-button flex-1 justify-center"
            :disabled="isApplyingCustomization"
            @click="applyCustomization"
        >
            <template v-if="isApplyingCustomization">
                Saving...
            </template>
            <template v-else>
                @lang('admin::app.settings.webforms.form.apply')
            </template>
        </button>
    </x-slot:footer>
</x-admin::drawer>
