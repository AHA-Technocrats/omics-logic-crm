<header class="admin-header">
    <div class="admin-header__left">
        <x-admin::layouts.sidebar.mobile />

        <div class="relative flex items-center">
            <!-- <i class="icon-search absolute top-1/2 -translate-y-1/2 text-lg text-gray-400 ltr:left-3 rtl:right-3"></i> -->

            <input
                type="text"
                class="admin-header__search px-9 outline-none transition-all focus:border-gray-300 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300"
                placeholder="Search contacts, emails, organizations, userid..."
            >
        </div>
    </div>

    <div class="admin-header__actions">
        <x-admin::datagrid.export :src="route('admin.contacts.persons.index')" />

        @if (bouncer()->hasPermission('contacts.persons.create'))
            <a
                href="{{ route('admin.contacts.persons.create') }}"
                class="admin-header__button admin-header__button--primary"
            >
                <i class="ti ti-plus text-sm"></i>
                Add contact
            </a>
        @endif
    </div>
</header>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dark-template"
    >
        <div class="flex">
            <span
                class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
                :class="[isDarkMode ? 'icon-light' : 'icon-dark']"
                @click="toggle"
            ></span>
        </div>
    </script>

    <script type="module">
        app.component('v-dark', {
            template: '#v-dark-template',

            data() {
                return {
                    isDarkMode: {{ request()->cookie('dark_mode') ?? 0 }},

                    logo: "{{ vite()->asset('images/logo.svg') }}",

                    dark_logo: "{{ vite()->asset('images/dark-logo.svg') }}",
                };
            },

            methods: {
                toggle() {
                    this.isDarkMode = parseInt(this.isDarkModeCookie()) ? 0 : 1;

                    var expiryDate = new Date();

                    expiryDate.setMonth(expiryDate.getMonth() + 1);

                    document.cookie = 'dark_mode=' + this.isDarkMode + '; path=/; expires=' + expiryDate.toGMTString();

                    document.documentElement.classList.toggle('dark', this.isDarkMode === 1);

                    if (this.isDarkMode) {
                        this.$emitter.emit('change-theme', 'dark');

                        document.getElementById('logo-image').src = this.dark_logo;
                    } else {
                        this.$emitter.emit('change-theme', 'light');

                        document.getElementById('logo-image').src = this.logo;
                    }
                },

                isDarkModeCookie() {
                    const cookies = document.cookie.split(';');

                    for (const cookie of cookies) {
                        const [name, value] = cookie.trim().split('=');

                        if (name === 'dark_mode') {
                            return value;
                        }
                    }

                    return 0;
                },
            },
        });
    </script>
@endPushOnce
