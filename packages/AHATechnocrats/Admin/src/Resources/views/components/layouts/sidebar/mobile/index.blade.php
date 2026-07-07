<v-sidebar-drawer>
    <i class="icon-menu lg:hidden cursor-pointer rounded-md p-1.5 text-2xl hover:bg-gray-100 dark:hover:bg-gray-950 max-lg:block"></i>
</v-sidebar-drawer>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-sidebar-drawer-template"
    >
        <x-admin::drawer
            position="left"
            width="280px"
            class="lg:hidden [&>:nth-child(3)]:!m-0 [&>:nth-child(3)]:!rounded-l-none [&>:nth-child(3)]:max-sm:!w-[80%]"
        >
            <x-slot:toggle>
                <i class="icon-menu lg:hidden cursor-pointer rounded-md p-1.5 text-2xl hover:bg-gray-100 dark:hover:bg-gray-950 max-lg:block"></i>
            </x-slot>

            <x-slot:header>
                @if ($logo = core()->getConfigData('general.general.admin_logo.logo_image'))
                    <img
                        class="h-10"
                        src="{{ Storage::url($logo) }}"
                        alt="{{ config('app.name') }}"
                    />
                @else
                    <img
                        class="h-10"
                        src="{{ request()->cookie('dark_mode') ? vite()->asset('images/dark-logo.svg') : vite()->asset('images/logo.svg') }}"
                        id="logo-image"
                        alt="{{ config('app.name') }}"
                    />
                @endif
            </x-slot>

            <x-slot:content class="p-4">
                <div class="journal-scroll h-[calc(100vh-100px)] overflow-auto">
                    <nav class="grid w-full gap-2">
                        @foreach (menu()->getItems('admin') as $menuItem)
                            <div class="menu-item relative" data-menu-key="{{ $menuItem->getKey() }}">
                                <a
                                    href="{{ $menuItem->getUrl() }}"
                                    class="menu-link flex items-center gap-3 rounded-lg p-2 transition-colors duration-200"
                                    :class="{ 'bg-brandColor text-white': {{ $menuItem->isActive() ? 'true' : 'false' }}, 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-950': {{ ! $menuItem->isActive() ? 'true' : 'false' }} }"
                                >
                                    <span class="{{ $menuItem->getIcon() }} text-2xl"></span>

                                    <p class="whitespace-nowrap font-semibold">{{ $menuItem->getName() }}</p>
                                </a>
                            </div>
                        @endforeach
                    </nav>
                </div>
            </x-slot>
        </x-admin::drawer>
    </script>

    <script type="module">
        app.component('v-sidebar-drawer', {
            template: '#v-sidebar-drawer-template',
        });
    </script>
@endPushOnce
