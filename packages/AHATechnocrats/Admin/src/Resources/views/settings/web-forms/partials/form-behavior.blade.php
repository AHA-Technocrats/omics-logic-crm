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

<div class="flex flex-col gap-6">
    <div>
        <p class="mb-1 text-base font-semibold text-gray-800 dark:text-white">
            @lang($langPrefix.'.step-after-submit')
        </p>

        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
            What happens when someone completes the form.
        </p>

        <div class="grid grid-cols-1 gap-4">
            <x-admin::form.control-group class="!mb-0">
                <x-admin::form.control-group.label class="required">
                    @lang($langPrefix.'.submit-success-action')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="select"
                    name="submit_success_action"
                    id="submit_success_action"
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

            <div v-show="submitSuccessAction.value === 'message'">
                <x-admin::form.control-group class="!mb-0">
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
                    :disabled="submitSuccessAction.value !== 'message'"
                />
            </div>

            <div v-show="submitSuccessAction.value === 'redirect'">
                <x-admin::form.control-group class="!mb-0">
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
                        ::disabled="submitSuccessAction.value !== 'redirect'"
                    />

                    <x-admin::form.control-group.error control-name="submit_success_content"/>
                </x-admin::form.control-group>
            </div>
        </div>
    </div>
</div>
