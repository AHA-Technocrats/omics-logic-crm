<div
    ref="sidebar"
    class="duration-80 fixed top-[3.75rem] sidebar-scroll z-[10002] h-full w-[12.5rem] border-gray-300 bg-white pt-4 transition-all group-[.sidebar-collapsed]/container:w-[4.375rem] dark:border-gray-800 dark:bg-gray-900 max-lg:hidden ltr:border-r rtl:border-l"
    @mouseover="handleMouseOver"
    @mouseleave="handleMouseLeave"
    style="height: calc(100vh - 67px); overflow-y: auto; overflow-x: clip;"
>
    <div class="journal-scroll h-[calc(100vh-6.25rem)] overflow-hidden group-[.sidebar-collapsed]/container:overflow-visible">
        <nav class="sidebar-rounded grid w-full gap-2">
            @foreach (menu()->getItems('admin') as $menuItem)
                <div class="px-4 group/item {{ $menuItem->isActive() ? 'active' : 'inactive' }}">
                    <a
                        class="flex gap-2 p-1.5 items-center cursor-pointer hover:rounded-lg {{ $menuItem->isActive() ? 'bg-brandColor rounded-lg text-white' : ' hover:bg-gray-100 hover:dark:bg-gray-950' }} peer"
                        href="{{ $menuItem->getUrl() }}"
                    >
                        <span class="{{ $menuItem->getIcon() }} text-2xl {{ $menuItem->isActive() ? 'text-white' : ''}}"></span>

                        <div class="flex-1 flex justify-between items-center text-gray-600 dark:text-gray-300 font-medium whitespace-nowrap group-[.sidebar-collapsed]/container:hidden {{ $menuItem->isActive() ? 'text-white' : ''}} group">
                            <p>{{ core()->getConfigData('general.settings.menu.'.$menuItem->getKey()) ?: $menuItem->getName() }}</p>
                        </div>
                    </a>
                </div>
            @endforeach
        </nav>
    </div>
</div>
