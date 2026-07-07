<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.contacts.persons.index.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs name="contacts.persons" />

                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.contacts.persons.index.title')
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-300">
                    @lang('omicslogic::app.contacts.subtitle')
                </p>
            </div>

            <div class="flex items-center gap-x-2.5">
                <x-admin::datagrid.export :src="route('admin.contacts.persons.index')" />

                @if (bouncer()->hasPermission('persons.create'))
                    <a href="{{ route('admin.contacts.persons.create') }}" class="primary-button">
                        @lang('omicslogic::app.contacts.add-btn')
                    </a>
                @endif
            </div>
        </div>

        <x-admin::datagrid :src="route('admin.contacts.persons.index')">
            <x-admin::shimmer.datagrid />
        </x-admin::datagrid>
    </div>
</x-admin::layouts>
