@php
    $user = auth()->guard('user')->user();

    $sections = [
        [
            'label' => 'START HERE',
            'items' => [
                [
                    'label' => 'Guide & roadmap',
                    'icon' => null,
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
                    'icon' => 'ti ti-users',
                    'route' => 'admin.contacts.persons.index',
                    'active' => request()->routeIs('admin.contacts.persons.*'),
                ],
                [
                    'label' => 'Organizations',
                    'icon' => 'ti ti-building',
                    'route' => 'admin.contacts.organizations.index',
                    'active' => request()->routeIs('admin.contacts.organizations.*'),
                ],
                [
                    'label' => 'Campaigns',
                    'icon' => 'ti ti-flag',
                    'route' => 'admin.leads.index',
                    'active' => request()->routeIs('admin.leads.*'),
                ],
                [
                    'label' => 'Merge review',
                    'icon' => null,
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
                    'icon' => 'ti ti-bookmark',
                    'route' => 'admin.segment.index',
                    'active' => request()->routeIs('admin.segment.*'),
                ],
                [
                    'label' => 'Reports',
                    'icon' => 'ti ti-chart-bar',
                    'route' => 'admin.dashboard.index',
                    'active' => false,
                ],
                [
                    'label' => 'Imports',
                    'icon' => 'ti ti-database-import',
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
                    'icon' => 'ti ti-plug-connected',
                    'route' => 'admin.configuration.index',
                    'active' => request()->routeIs('admin.configuration.*'),
                ],
                [
                    'label' => 'Audit log',
                    'icon' => 'ti ti-history',
                    'route' => 'admin.settings.index',
                    'active' => false,
                ],
                [
                    'label' => 'Settings',
                    'icon' => 'ti ti-settings',
                    'route' => 'admin.settings.index',
                    'active' => request()->routeIs('admin.settings.*') && ! request()->routeIs('admin.settings.data_transfer.imports.*'),
                ],
            ],
        ],
    ];
@endphp

<aside
    ref="sidebar"
    class="admin-fixed-sidebar fixed inset-y-0 z-[10002] flex w-[286px] flex-col overflow-hidden shadow-xl shadow-slate-950/15 transition-all max-lg:hidden"
>
    <a
        href="{{ route('admin.dashboard.index') }}"
        class="crm-sidebar__brand"
    >
        <span class="crm-sidebar__logo-mark">O</span>
        <span>OmicsLogic CRM</span>
    </a>

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
</aside>