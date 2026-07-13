<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.webforms.index.title')
    </x-slot>

    <v-webform>
        <div class="flex flex-col gap-4">
            <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    <!-- Bredcrumbs -->
                    <x-admin::breadcrumbs name="web_forms" />
        
                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.settings.webforms.index.title')
                    </div>
                </div>
        
                <div class="flex items-center gap-x-2.5">
                    <!-- Create button for person -->
                    <div class="flex items-center gap-x-2.5">
                        @if (bouncer()->hasPermission('web_forms.create'))
                            <button
                                type="button"
                                class="primary-button"
                            >
                                @lang('admin::app.settings.webforms.index.create-btn')
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        
            <!-- DataGrid Shimmer -->
            <x-admin::shimmer.settings.web-forms />
        </div>
    </v-webform>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-webform-template"
        >
            <div class="flex flex-col gap-4">
                <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    <div class="flex flex-col gap-2">
                        <!-- Bredcrumbs -->
                        <x-admin::breadcrumbs name="web_forms" />
            
                        <div class="text-xl font-bold dark:text-white">
                            @lang('admin::app.settings.webforms.index.title')
                        </div>
                    </div>

                    <div class="flex items-center gap-x-2.5">
                        <!-- Create button for person -->
                        <div class="flex items-center gap-x-2.5">
                            {!! view_render_event('admin.settings.web_forms.index.create_button.before') !!}
            
                            @if (bouncer()->hasPermission('web_forms.create'))
                                <a
                                    href="{{ route('admin.web_forms.create') }}"
                                    class="primary-button"
                                >
                                    @lang('admin::app.settings.webforms.index.create-btn')
                                </a>
                            @endif

                            {!! view_render_event('admin.settings.web_forms.index.create_button.after') !!}
                        </div>
                    </div>
                </div>
            
                {!! view_render_event('admin.settings.web_forms.index.datagrid.before') !!}

                <!-- Datagrid -->
                <x-admin::datagrid
                    :src="route('admin.web_forms.index')"
                    ref="datagrid"
                >
                    <template #header="{
                        isLoading,
                        available,
                        applied,
                        selectAll,
                        sort,
                        performAction
                    }">
                        <template v-if="isLoading">
                            <x-admin::shimmer.settings.web-forms.head />
                        </template>
                    </template>

                    <template #body="{
                        isLoading,
                        available,
                        applied,
                        selectAll,
                        sort,
                        performAction
                    }">
                        <template v-if="isLoading">
                            <x-admin::shimmer.settings.web-forms.body />
                        </template>
                    </template>
                </x-admin::datagrid>

                {!! view_render_event('admin.settings.web_forms.index.datagrid.after') !!}

                <!-- Embed Modal -->
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
                                ::value="previewUrl"
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
                                :value="generatedScriptTag"
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
            app.component('v-webform', {
                template: '#v-webform-template',
                data() {
                    return {
                        previewUrl: '',
                        scriptUrl: ''
                    };
                },
                computed: {
                    generatedScriptTag() {
                        if (!this.scriptUrl) return '';
                        return '<script src="' + this.scriptUrl + '"><\/script>';
                    }
                },
                mounted() {
                    window.openWebFormEmbedModal = (previewUrl, scriptUrl) => {
                        this.previewUrl = previewUrl;
                        this.scriptUrl = scriptUrl;
                        this.$refs.embed.toggle();
                    };
                },
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
