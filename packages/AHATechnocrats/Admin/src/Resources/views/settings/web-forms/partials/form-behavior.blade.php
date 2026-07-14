@php
    $mode = $mode ?? 'create';
    $webForm = $webForm ?? null;
    $langPrefix = $mode === 'edit' ? 'admin::app.settings.webforms.edit' : 'admin::app.settings.webforms.create';
    $defaultThankYou = \AHATechnocrats\WebForm\Models\WebForm::DEFAULT_THANK_YOU_CONTENT;
    $thankYouContent = old(
        'thank_you_content',
        $webForm?->thank_you_content
            ?: ($webForm?->resolvedThankYouContent() ?? $defaultThankYou)
    );
@endphp

<div class="border-t border-gray-200 pt-6 dark:border-gray-800">
    <p class="mb-1 text-base font-semibold text-gray-800 dark:text-white">
        After submit
    </p>

    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
        What happens when someone completes the form.
    </p>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <x-admin::form.control-group class="!mb-0 md:col-span-2">
            <x-admin::form.control-group.label class="required">
                @lang($langPrefix.'.submit-success-action')
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="select"
                name="submit_success_action"
                id="submit_success_action"
                value="message"
                class="w-full sm:w-auto sm:min-w-[16rem]"
                :label="trans($langPrefix.'.submit-success-action')"
                v-model="submitSuccessAction.value"
            >
                <template
                    v-for="(option, index) in submitSuccessAction.options"
                    :key="index"
                >
                    <option
                        :value="option.value"
                        :text="option.label"
                    ></option>
                </template>
            </x-admin::form.control-group.control>
        </x-admin::form.control-group>

        <template v-if="submitSuccessAction.value === 'message'">
            <x-admin::form.control-group class="!mb-0 md:col-span-2">
                <x-admin::form.control-group.label class="required">
                    @lang($langPrefix.'.thank-you-page')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="textarea"
                    id="thank_you_content"
                    name="thank_you_content"
                    :tinymce="true"
                    rules="required"
                    :value="$thankYouContent"
                    :label="trans($langPrefix.'.thank-you-page')"
                    :placeholder="trans($langPrefix.'.thank-you-page')"
                />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang($langPrefix.'.thank-you-page-info')
                </p>

                <x-admin::form.control-group.error control-name="thank_you_content"/>
            </x-admin::form.control-group>

            <input
                type="hidden"
                name="submit_success_content"
                value="{{ old('submit_success_content', $webForm?->submit_success_content ?: 'Your response has been recorded.') }}"
            />
        </template>

        <template v-else>
            <x-admin::form.control-group class="!mb-0 md:col-span-2">
                <x-admin::form.control-group.label class="required">
                    @lang($langPrefix.'.redirect-url')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    name="submit_success_content"
                    id="submit_success_content"
                    class="w-full"
                    rules="required"
                    :value="old('submit_success_content', $webForm?->submit_success_content)"
                    :label="trans($langPrefix.'.redirect-url')"
                    ::placeholder="placeholder"
                />

                <x-admin::form.control-group.error control-name="submit_success_content"/>
            </x-admin::form.control-group>
        </template>

        <x-admin::form.control-group class="!mb-0">
            <x-admin::form.control-group.label for="create_lead" @class(['required' => $mode === 'edit'])>
                @lang($langPrefix.'.create-lead')
            </x-admin::form.control-group.label>

            <input
                type="hidden"
                name="create_lead"
                :value="0"
            />

            <x-admin::form.control-group.control
                type="switch"
                name="create_lead"
                value="1"
                :label="trans($langPrefix.'.create-lead')"
                :checked="(bool) old('create_lead', $webForm?->create_lead ?? false)"
                @change="onCreateLeadChange"
            />
        </x-admin::form.control-group>
    </div>
</div>
