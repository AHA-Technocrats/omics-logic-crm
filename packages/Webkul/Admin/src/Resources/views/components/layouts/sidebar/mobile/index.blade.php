@php
    $user = auth()->guard('user')->user();

    $sections = [
        [
            'label' => 'START HERE',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'fa-regular fa-map',
                    'route' => 'admin.dashboard.index',
                    'active' => request()->routeIs('admin.dashboard.*'),
                    'badge' => ['text' => 'New', 'class' => 'crm-sidebar__badge--green'],
                ],
            ],
        ],
        [
            'label' => 'DATA',
            'items' => [
                [
                    'label' => 'Contacts',
                    'icon' => 'fa-solid fa-users',
                    'route' => 'admin.contacts.persons.index',
                    'active' => request()->routeIs('admin.contacts.persons.*'),
                ],
                [
                    'label' => 'Organizations',
                    'icon' => 'fa-regular fa-building',
                    'route' => 'admin.contacts.organizations.index',
                    'active' => request()->routeIs('admin.contacts.organizations.*'),
                ],
                [
                    'label' => 'Campaigns',
                    'icon' => 'fa-regular fa-flag',
                    'route' => 'admin.leads.index',
                    'active' => request()->routeIs('admin.leads.*'),
                ],
                [
                    'label' => 'Merge review',
                    'icon' => 'fa-solid fa-code-merge',
                    'route' => 'admin.leads.index',
                    'active' => false,
                    'badge' => ['text' => '23', 'class' => 'crm-sidebar__badge--orange'],
                ],
            ],
        ],
        [
            'label' => 'WORK',
            'items' => [
                [
                    'label' => 'Segments',
                    'icon' => 'fa-regular fa-bookmark',
                    'route' => 'admin.segment.index',
                    'active' => request()->routeIs('admin.segment.*'),
                ],
                [
                    'label' => 'Reports',
                    'icon' => 'fa-solid fa-chart-simple',
                    'route' => 'admin.report.index',
                    'active' => false,
                ],
                [
                    'label' => 'Imports',
                    'icon' => 'fa-solid fa-database',
                    'route' => 'admin.settings.data_transfer.imports.index',
                    'active' => request()->routeIs('admin.settings.data_transfer.imports.*'),
                ],
            ],
        ],
        [
            'label' => 'SYSTEM',
            'items' => [
                [
                    'label' => 'Connectors',
                    'icon' => 'fa-solid fa-plug',
                    'route' => 'admin.configuration.index',
                    'active' => request()->routeIs('admin.configuration.*'),
                ],
                [
                    'label' => 'Audit log',
                    'icon' => 'fa-solid fa-clock-rotate-left',
                    'route' => 'admin.audit-log.index',
                    'active' => request()->routeIs('admin.audit-log.*'),
                ],
                [
                    'label' => 'Settings',
                    'icon' => 'fa-solid fa-gear',
                    'route' => 'admin.settings.index',
                    'active' => request()->routeIs('admin.settings.*') && ! request()->routeIs('admin.settings.data_transfer.imports.*'),
                ],
            ],
        ],
    ];
@endphp

<v-sidebar-drawer>
    <button
        type="button"
        class="admin-mobile-menu-trigger"
        aria-label="Open navigation menu"
    >
        <i class="fa-solid fa-bars"></i>
    </button>
</v-sidebar-drawer>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-sidebar-drawer-template"
    >
        <x-admin::drawer
            position="left"
            width="286px"
            class="lg:hidden [&>:nth-child(3)]:!m-0 [&>:nth-child(3)]:!rounded-l-none [&>:nth-child(3)]:max-sm:!w-[86%]"
        >
            <x-slot:toggle>
                <button
                    type="button"
                    class="admin-mobile-menu-trigger"
                    aria-label="Open navigation menu"
                >
                    <i class="fa-solid fa-bars"></i>
                </button>
            </x-slot>

            <x-slot:header class="crm-sidebar-mobile__header">
                <a
                    href="{{ route('admin.dashboard.index') }}"
                    class="crm-sidebar__brand"
                >
                    <span class="crm-sidebar__logo-mark">O</span>
                    <span>OmicsLogic CRM</span>
                </a>
            </x-slot>

            <x-slot:content class="crm-sidebar-mobile__content">
                <div class="crm-sidebar-mobile">
                    <div class="crm-sidebar__scroll">
                        <nav class="grid gap-1">
                            @foreach ($sections as $section)
                                <div class="crm-sidebar__section">
                                    <p class="crm-sidebar__section-title">{{ $section['label'] }}</p>

                                    <div class="grid gap-1">
                                        @foreach ($section['items'] as $item)
                                            <a
                                                href="{{ route($item['route']) }}"
                                                class="crm-sidebar__link {{ $item['active'] ? 'is-active' : '' }}"
                                            >
                                                <span class="crm-sidebar__icon">
                                                    @if ($item['icon'])
                                                        <i class="{{ $item['icon'] }}"></i>
                                                    @endif
                                                </span>

                                                <span class="crm-sidebar__label">{{ $item['label'] }}</span>

                                                @isset($item['badge'])
                                                    <span class="crm-sidebar__badge {{ $item['badge']['class'] }}">
                                                        {{ $item['badge']['text'] }}
                                                    </span>
                                                @endisset
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </nav>
                    </div>

                    <div class="crm-sidebar__profile">
                        <span class="crm-sidebar__avatar">
                            {{ $user ? collect(explode(' ', $user->name))->filter()->map(fn ($part) => substr($part, 0, 1))->take(2)->implode('') : 'OM' }}
                        </span>

                        <div>
                            <p class="crm-sidebar__profile-name">{{ $user?->name ?? 'Admin' }}</p>
                            <p class="crm-sidebar__profile-role">Admin</p>
                        </div>
                    </div>
                </div>
            </x-slot>
        </x-admin::drawer>
    </script>

    <script type="module">
        app.component('v-sidebar-drawer', {
            template: '#v-sidebar-drawer-template',

            data() {
                return {};
            },
        });
    </script>
@endPushOnce
