@php
    $mode = $mode ?? 'create';
    $webForm = $webForm ?? null;
    $langPrefix = $mode === 'edit' ? 'admin::app.settings.webforms.edit' : 'admin::app.settings.webforms.create';
@endphp

<div class="flex flex-col gap-6">
    <!-- General -->
    <div>
        <p class="mb-1 text-base font-semibold text-gray-800 dark:text-white">
            @lang($langPrefix.'.general')
        </p>

        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
            Form title, description, and submit button label shown to visitors.
        </p>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-admin::form.control-group class="!mb-0">
                <x-admin::form.control-group.label class="required">
                    @lang($langPrefix.'.title')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    name="title"
                    rules="required"
                    :value="old('title', $webForm?->title)"
                    :label="trans($langPrefix.'.title')"
                    :placeholder="trans($langPrefix.'.title')"
                    v-model="formTitle"
                />

                <x-admin::form.control-group.error control-name="title" />
            </x-admin::form.control-group>

            <x-admin::form.control-group class="!mb-0">
                <x-admin::form.control-group.label class="required">
                    @lang($langPrefix.'.submit-button-label')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    name="submit_button_label"
                    rules="required"
                    :value="old('submit_button_label', $webForm?->submit_button_label ?? 'Submit')"
                    :label="trans($langPrefix.'.submit-button-label')"
                    v-model="submitButtonLabel"
                />

                <x-admin::form.control-group.error control-name="submit_button_label" />
            </x-admin::form.control-group>
        </div>

        <x-admin::form.control-group class="!mb-0 mt-4">
            <x-admin::form.control-group.label>
                @lang($langPrefix.'.description')
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="textarea"
                id="description"
                name="description"
                :tinymce="true"
                :value="old('description', $webForm?->description)"
                :label="trans($langPrefix.'.description')"
                :placeholder="trans($langPrefix.'.description')"
            />

            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Use line breaks, lists, and formatting — the description renders on the public form as written.
            </p>

            <x-admin::form.control-group.error control-name="description" />
        </x-admin::form.control-group>
    </div>

    <!-- Status & notifications -->
    <div class="border-t border-gray-200 pt-6 dark:border-gray-800">
        <p class="mb-1 text-base font-semibold text-gray-800 dark:text-white">
            Status &amp; notifications
        </p>

        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
            Control whether the form is live and if submitters receive a confirmation email.
        </p>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-admin::form.control-group class="!mb-0">
                <x-admin::form.control-group.label for="is_active">
                    @lang('admin::app.settings.webforms.form.is-active')
                </x-admin::form.control-group.label>

                <input type="hidden" name="is_active" :value="0" />

                <x-admin::form.control-group.control
                    type="switch"
                    name="is_active"
                    value="1"
                    :label="trans('admin::app.settings.webforms.form.is-active')"
                    :checked="(bool) old('is_active', $webForm?->is_active ?? true)"
                    v-model="isActive"
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
                v-if="sendSubmitterEmail"
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
