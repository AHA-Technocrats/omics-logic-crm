<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.webforms.responses.title', ['title' => $webForm->title])
    </x-slot>

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
    </div>
</x-admin::layouts>
