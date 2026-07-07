<x-admin::layouts>
    <x-slot:title>
        @lang('omicslogic::app.segments.create-title')
    </x-slot>

    <x-admin::form :action="route('admin.omics.segments.store')" method="POST">
        <div class="flex flex-col gap-4">
            <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div>
                    <x-admin::breadcrumbs name="omics.segments.create" />
                    <div class="text-xl font-bold dark:text-white">
                        @lang('omicslogic::app.segments.create-title')
                    </div>
                </div>
                <button type="submit" class="primary-button">@lang('omicslogic::app.segments.save-btn')</button>
            </div>

            <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                @include('admin::omics.partials.segment-fields')
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>
