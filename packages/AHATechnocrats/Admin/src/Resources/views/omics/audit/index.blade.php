<x-admin::layouts>
    <x-slot:title>
        @lang('omicslogic::app.audit.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <x-admin::breadcrumbs name="omics.audit" />
            <div class="text-xl font-bold dark:text-white">
                @lang('omicslogic::app.audit.title')
            </div>
        </div>

        <x-admin::datagrid :src="route('admin.omics.audit.index')">
            <x-admin::shimmer.datagrid />
        </x-admin::datagrid>
    </div>
</x-admin::layouts>
