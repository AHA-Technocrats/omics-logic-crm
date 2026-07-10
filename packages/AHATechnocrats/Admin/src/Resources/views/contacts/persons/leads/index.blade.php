<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.contacts.persons.leads.title', ['name' => strip_tags($person->name)])
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs
                    name="contacts.persons.leads"
                    :entity="$person"
                />

                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.contacts.persons.leads.title', ['name' => $person->name])
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-300">
                    @lang('admin::app.contacts.persons.leads.subtitle')
                </p>
            </div>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.contacts.persons.view', $person->id) }}"
                    class="secondary-button"
                >
                    @lang('admin::app.contacts.persons.leads.back-btn')
                </a>
            </div>
        </div>

        <x-admin::datagrid :src="route('admin.contacts.persons.leads.index', $person->id)">
            <x-admin::shimmer.datagrid />
        </x-admin::datagrid>
    </div>
</x-admin::layouts>
