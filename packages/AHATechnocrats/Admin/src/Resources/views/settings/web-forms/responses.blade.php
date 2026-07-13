<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.webforms.responses.title', ['title' => $webForm->title])
    </x-slot>

        <v-web-form-responses></v-web-form-responses>

        @pushOnce('scripts')
            <script
                type="text/x-template"
                id="v-web-form-responses-template"
            >
                <div class="flex flex-col gap-4">
                    <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                        <div class="flex flex-col gap-2">
                            <x-admin::breadcrumbs
                                name="web_forms.responses"
                                :entity="$webForm"
                            />

                            <div class="text-xl font-bold dark:text-white">
                                @lang('admin::app.settings.webforms.responses.heading', ['title' => $webForm->title])
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @lang('admin::app.settings.webforms.responses.subtitle', ['count' => $submissionCount])
                            </p>
                        </div>

                        <div class="flex items-center gap-2.5">
                            <a href="{{ route('admin.settings.web_forms.preview', $webForm->form_id) }}" target="_blank" class="secondary-button">
                                @lang('admin::app.settings.webforms.edit.preview')
                            </a>

                            <button
                                type="button"
                                class="secondary-button"
                                @click="$refs.embed.toggle()"
                            >
                                @lang('admin::app.settings.webforms.edit.embed')
                            </button>

                            <a href="{{ route('admin.web_forms.edit', $webForm->id) }}" class="secondary-button">
                                @lang('admin::app.settings.webforms.responses.edit-form')
                            </a>

                            <a href="{{ route('admin.web_forms.responses.export', $webForm->id) }}" class="secondary-button">
                                <span class="icon-download text-lg ltr:mr-1 rtl:ml-1"></span>
                                @lang('admin::app.settings.webforms.responses.export-excel')
                            </a>
                        </div>
                    </div>

                    <x-admin::datagrid :src="route('admin.web_forms.responses.index', $webForm->id)" />

                    <x-admin::modal ref="embed">
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.webforms.edit.preview')
                            </p>
                        </x-slot>

                        <x-slot:content class="!border-b-0">
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
                                    value="{{ '<script src=\"'.route('admin.settings.web_forms.form_js', $webForm->form_id).'\"></script>' }}"
                                />

                                <span
                                    id="coeSnippt"
                                    class="cursor-pointer text-xs font-normal text-brandColor hover:text-sky-600 hover:underline"
                                    @click="copyToClipboard('#codeSnippet','#coeSnippt')"
                                >
                                    @lang('admin::app.settings.webforms.edit.copy')
                                </span>
                            </x-admin::form.control-group>
                        </x-slot>
                    </x-admin::modal>
                </div>
            </script>

            <script type="module">
                app.component('v-web-form-responses', {
                    template: '#v-web-form-responses-template',
                    methods: {
                        copyToClipboard(elementId, btnId) {
                            const inputElement = this.$el.querySelector(elementId);
                            const btnElement = this.$el.querySelector(btnId);
                            inputElement.select();
                            document.execCommand("copy");
                            btnElement.textContent = "@lang('admin::app.settings.webforms.edit.copied')!";
                            setTimeout(() => btnElement.textContent = "Copy", 1000);
                        }
                    }
                });
            </script>
        @endPushOnce
</x-admin::layouts>
