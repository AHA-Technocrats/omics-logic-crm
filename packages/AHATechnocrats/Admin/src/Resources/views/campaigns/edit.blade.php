
<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.campaigns.edit.title')
    </x-slot>

    {!! view_render_event('admin.campaigns.edit.form.before') !!}

    <x-admin::form
        :action="route('admin.campaigns.update', $product->id)"
        encType="multipart/form-data"
        method="PUT"
    >
        <div class="flex flex-col gap-4">
            <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    <!-- Breadcrumbs -->
                    <x-admin::breadcrumbs
                        name="campaigns.edit"
                        :entity="$product"
                     />

                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.campaigns.edit.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.campaigns.edit.create_button.before', ['product' => $product]) !!}
                        
                        <!-- Edit button for Product -->
                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('admin::app.campaigns.create.save-btn')
                        </button>

                        {!! view_render_event('admin.campaigns.edit.create_button.after', ['product' => $product]) !!}
                    </div>
                </div>
            </div>

            <div class="flex gap-2.5 max-xl:flex-wrap">
                <!-- Left sub-component -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                    <div class="box-shadow rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.campaigns.create.general')
                        </p>

                        {!! view_render_event('admin.campaigns.edit.attributes.before', ['product' => $product]) !!}

                        <x-admin::attributes
                            :custom-attributes="app('AHATechnocrats\Attribute\Repositories\AttributeRepository')->findWhere([
                                'entity_type' => 'products',
                                ['code', 'NOTIN', ['price', 'quantity']],
                            ])"
                            :entity="$product"
                        />

                        @include('admin::omics.partials.campaign-fields', ['record' => $product])

                        {!! view_render_event('admin.campaigns.edit.attributes.after', ['product' => $product]) !!}
                    </div>
                </div>

                <!-- Right sub-component -->
                <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                    {!! view_render_event('admin.campaigns.edit.accordion.before', ['product' => $product]) !!}

                    <x-admin::accordion >
                        <x-slot:header>
                            {!! view_render_event('admin.campaigns.edit.accordion.header.before', ['product' => $product]) !!}

                            <div class="flex items-center justify-between">
                                <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('admin::app.campaigns.create.price')
                                </p>
                            </div>

                            {!! view_render_event('admin.campaigns.edit.accordion.header.after', ['product' => $product]) !!}
                        </x-slot>

                        <x-slot:content>
                            {!! view_render_event('admin.campaigns.edit.accordion.content.attributes.before', ['product' => $product]) !!}

                            <x-admin::attributes
                                :custom-attributes="app('AHATechnocrats\Attribute\Repositories\AttributeRepository')->findWhere([
                                    'entity_type' => 'products',
                                    ['code', 'IN', ['price', 'quantity']],
                                ])"
                                :entity="$product"
                            />

                            {!! view_render_event('admin.campaigns.edit.accordion.content.attributes.after', ['product' => $product]) !!}
                        </x-slot>
                    </x-admin::accordion>

                    {!! view_render_event('admin.campaigns.edit.accordion.after', ['product' => $product]) !!}
                </div>
            </div>
        </div>
    </x-admin::form>

    {!! view_render_event('admin.campaigns.edit.form.after') !!}

    @push('scripts')
        <script>
            document.addEventListener('focus', function (event) {
                if (event.target && (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA')) {
                    const inputTypes = ['text', 'number', 'email', 'search', 'url', 'tel', 'password'];
                    if (event.target.tagName === 'TEXTAREA' || inputTypes.includes(event.target.type || 'text')) {
                        setTimeout(function() {
                            if (document.activeElement === event.target) {
                                event.target.select();
                            }
                        }, 50);
                    }
                }
            }, true);
        </script>
    @endpush
</x-admin::layouts>
