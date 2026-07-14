@php
    $mode = $mode ?? 'create';
    $webForm = $webForm ?? null;
    $langPrefix = $mode === 'edit' ? 'admin::app.settings.webforms.edit' : 'admin::app.settings.webforms.create';
@endphp

<div class="flex flex-col gap-6">
    <div>
        <p class="mb-1 text-base font-semibold text-gray-800 dark:text-white">
            @lang($langPrefix.'.step-lead-email')
        </p>

        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
            Configure lead creation and confirmation emails for submitters.
        </p>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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

            <x-admin::form.control-group class="!mb-0">
                <x-admin::form.control-group.label for="send_submitter_email">
                    @lang('admin::app.settings.webforms.form.send-submitter-email')
                </x-admin::form.control-group.label>

                <input type="hidden" name="send_submitter_email" :value="0" />

                <x-admin::form.control-group.control
                    type="switch"
                    name="send_submitter_email"
                    value="1"
                    :label="trans('admin::app.settings.webforms.form.send-submitter-email')"
                    :checked="(bool) old('send_submitter_email', $webForm?->send_submitter_email ?? false)"
                    v-model="sendSubmitterEmail"
                />
            </x-admin::form.control-group>

            <x-admin::form.control-group
                v-show="sendSubmitterEmail"
                class="!mb-0 md:col-span-2"
            >
                <x-admin::form.control-group.label>
                    @lang('admin::app.settings.webforms.form.email-template')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="select"
                    name="email_template_id"
                    :value="old('email_template_id', $webForm?->email_template_id)"
                    :label="trans('admin::app.settings.webforms.form.email-template')"
                    v-model="emailTemplateId"
                >
                    <option value="">@lang('admin::app.settings.webforms.form.select-email-template')</option>

                    @foreach ($emailTemplates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </x-admin::form.control-group.control>

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('admin::app.settings.webforms.form.email-template-help')
                </p>

                <x-admin::form.control-group.error control-name="email_template_id" />
            </x-admin::form.control-group>
        </div>
    </div>
</div>
