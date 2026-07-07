@php
    $mode = $mode ?? 'create';
    $webForm = $webForm ?? null;
    $langPrefix = $mode === 'edit' ? 'admin::app.settings.webforms.edit' : 'admin::app.settings.webforms.create';
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

            <div class="flex flex-col gap-2 sm:flex-row sm:items-start">
                <x-admin::form.control-group.control
                    type="select"
                    name="submit_success_action"
                    id="submit_success_action"
                    value="message"
                    class="w-full sm:w-auto sm:min-w-[12rem] sm:rounded-r-none"
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

                <x-admin::form.control-group.control
                    type="text"
                    name="submit_success_content"
                    id="submit_success_content"
                    class="w-full sm:flex-1 sm:rounded-l-none"
                    rules="required"
                    :value="old('submit_success_content', $webForm?->submit_success_content)"
                    :label="trans($langPrefix.'.submit-success-action')"
                    ::placeholder="placeholder"
                />
            </div>

            <x-admin::form.control-group.error control-name="submit_success_content"/>
        </x-admin::form.control-group>

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
