<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.mass_assign.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Header -->
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <!-- Breadcrumbs -->
                <div class="flex items-center gap-x-2 text-sm text-gray-500">
                    <a href="{{ route('admin.dashboard.index') }}" class="hover:text-brandColor">@lang('admin::app.dashboard.index.title')</a>
                    <span class="text-gray-400">/</span>
                    <a href="{{ route('admin.contacts.organizations.index') }}" class="hover:text-brandColor">@lang('admin::app.contacts.organizations.index.title')</a>
                    <span class="text-gray-400">/</span>
                    <span class="font-medium text-gray-800 dark:text-gray-300">@lang('admin::app.mass_assign.title')</span>
                </div>
                
                <div class="text-xl font-bold dark:text-gray-300">
                    @lang('admin::app.mass_assign.title')
                </div>
            </div>
        </div>

        <!-- Vue Component Mount -->
        <v-mass-assign></v-mass-assign>
    </div>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-mass-assign-template">
            <div class="flex gap-4 max-lg:flex-col">
                <!-- Left Panel: Organizations -->
                <div class="flex-1 rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 p-4 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                                Organizations (@{{ unassignedCount }} Unassigned / Admin)
                            </h3>
                            <div class="flex items-center gap-2">
                                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <input 
                                        type="checkbox" 
                                        @change="toggleAllOrgs($event)"
                                        :checked="selectedOrgs.length === organizations.length && organizations.length > 0"
                                        class="h-4 w-4 rounded border-gray-300 text-brandColor focus:ring-brandColor"
                                    >
                                    Select All
                                </label>
                                <input 
                                    type="text" 
                                    v-model="searchQuery"
                                    @input="searchEntities"
                                    placeholder="Search organizations..." 
                                    class="rounded-md border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                >
                            </div>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            Drag these organizations to the users on the right, or click Distribute.
                        </div>
                    </div>

                    <div class="h-[600px] overflow-y-auto p-4">
                        <div v-if="isLoadingEntities" class="text-center py-10">Loading...</div>
                        <draggable
                            v-else
                            v-model="organizations"
                            group="shared"
                            item-key="id"
                            class="flex flex-col gap-2 min-h-full"
                        >
                            <template #item="{element}">
                                <div class="cursor-move flex items-start gap-3 rounded-md border border-gray-200 bg-gray-50 p-3 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700" :class="{'border-brandColor bg-blue-50 dark:bg-blue-900/20': selectedOrgs.includes(element.id)}">
                                    <input 
                                        type="checkbox" 
                                        v-model="selectedOrgs" 
                                        :value="element.id" 
                                        class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brandColor focus:ring-brandColor"
                                    >
                                    <div>
                                        <div class="font-medium text-gray-800 dark:text-white">@{{ element.name }}</div>
                                        <div class="text-xs text-gray-500">Current Owner: @{{ element.account_owner_name || 'Unassigned' }}</div>
                                    </div>
                                </div>
                            </template>
                        </draggable>
                    </div>
                </div>

                <!-- Right Panel: Users -->
                <div class="flex-1 rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 p-4 dark:border-gray-800 flex justify-between items-center">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                            Available Users (@{{ users.length }})
                        </h3>
                        <div class="flex items-center gap-2">
                            <button 
                                @click="saveManualAssignments" 
                                class="primary-button text-sm bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700"
                                :disabled="isAssigning || !hasPendingAssignments"
                                :class="{'opacity-50': isAssigning || !hasPendingAssignments}"
                            >
                                <span v-if="isAssigning">Saving...</span>
                                <span v-else>Save Drop Assignments</span>
                            </button>
                            <button 
                                @click="distributeEvenly" 
                                class="primary-button text-sm"
                                :disabled="isAssigning || organizations.length === 0 || selectedUsers.length === 0"
                                :class="{'opacity-50': isAssigning || organizations.length === 0 || selectedUsers.length === 0}"
                            >
                                <span v-if="isAssigning">Processing...</span>
                                <span v-else>Distribute Evenly</span>
                            </button>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 text-sm">
                        <p>Select users to include in bulk distribution, or drag organizations directly into their boxes.</p>
                    </div>

                    <div class="h-[550px] overflow-y-auto p-4 flex flex-col gap-4">
                        <div v-if="isLoadingUsers" class="text-center py-10">Loading...</div>
                        
                        <div v-for="(user, index) in users" :key="user.id" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex items-center gap-3 mb-3 pb-2 border-b border-gray-100 dark:border-gray-700">
                                <input type="checkbox" v-model="selectedUsers" :value="user.id" class="h-4 w-4 rounded border-gray-300 text-brandColor focus:ring-brandColor">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600">
                                    @{{ user.name.charAt(0) }}
                                </div>
                                <div class="font-medium text-gray-800 dark:text-white">@{{ user.name }}</div>
                                <div class="ml-auto text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                    @{{ userAssignedOrgs[user.id] ? userAssignedOrgs[user.id].length : 0 }} Assigned
                                </div>
                            </div>

                            <draggable
                                v-model="userAssignedOrgs[user.id]"
                                group="shared"
                                item-key="id"
                                class="min-h-[60px] rounded-md border border-dashed border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-900/50"
                                @change="onDragChange($event, user.id)"
                            >
                                <template #item="{element}">
                                    <div class="mb-2 cursor-move rounded border border-blue-200 bg-blue-50 p-2 text-sm text-blue-800 dark:border-blue-900 dark:bg-blue-900/30 dark:text-blue-300">
                                        @{{ element.name }}
                                    </div>
                                </template>
                            </draggable>
                        </div>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-mass-assign', {
                template: '#v-mass-assign-template',

                data() {
                    return {
                        organizations: [],
                        users: [],
                        userAssignedOrgs: {},
                        selectedUsers: [],
                        selectedOrgs: [],
                        searchQuery: '',
                        isLoadingEntities: true,
                        isLoadingUsers: true,
                        isAssigning: false,
                        searchTimeout: null,
                    };
                },

                computed: {
                    unassignedCount() {
                        return this.organizations.length;
                    },
                    hasPendingAssignments() {
                        return Object.values(this.userAssignedOrgs).some(orgs => orgs.length > 0);
                    }
                },

                mounted() {
                    this.fetchEntities();
                    this.fetchUsers();
                },

                methods: {
                    fetchEntities() {
                        this.isLoadingEntities = true;
                        this.$axios.get("{{ route('admin.mass_assign.entities') }}", {
                            params: { search: this.searchQuery }
                        })
                        .then(response => {
                            this.organizations = response.data.data;
                            this.isLoadingEntities = false;
                        })
                        .catch(error => {
                            this.isLoadingEntities = false;
                            this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || 'Error fetching organizations' });
                        });
                    },

                    toggleAllOrgs(event) {
                        if (event.target.checked) {
                            this.selectedOrgs = this.organizations.map(org => org.id);
                        } else {
                            this.selectedOrgs = [];
                        }
                    },

                    fetchUsers() {
                        this.isLoadingUsers = true;
                        this.$axios.get("{{ route('admin.mass_assign.users') }}")
                        .then(response => {
                            this.users = response.data.data;
                            // Initialize assigned array and default select all
                            this.users.forEach(user => {
                                this.userAssignedOrgs[user.id] = [];
                                this.selectedUsers.push(user.id);
                            });
                            this.isLoadingUsers = false;
                        })
                        .catch(error => {
                            this.isLoadingUsers = false;
                            this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || 'Error fetching users' });
                        });
                    },

                    searchEntities() {
                        clearTimeout(this.searchTimeout);
                        this.searchTimeout = setTimeout(() => {
                            this.fetchEntities();
                        }, 500);
                    },

                    onDragChange(event, userId) {
                        if (event.added) {
                            const org = event.added.element;
                            
                            // If dragged organization is part of multi-select, move all selected organizations
                            if (this.selectedOrgs.includes(org.id) && this.selectedOrgs.length > 1) {
                                // Find the other selected orgs
                                const otherOrgs = this.organizations.filter(o => this.selectedOrgs.includes(o.id) && o.id !== org.id);
                                
                                // Push them into the user's bucket
                                this.userAssignedOrgs[userId].push(...otherOrgs);
                                
                                // Remove them from the left side
                                this.organizations = this.organizations.filter(o => !this.selectedOrgs.includes(o.id) || o.id === org.id);
                            }

                            // The left-side item that was physically dragged is already removed by VueDraggable
                            // Now clear the selection
                            this.selectedOrgs = [];
                        }
                    },

                    saveManualAssignments() {
                        const assignments = [];
                        
                        Object.keys(this.userAssignedOrgs).forEach(userId => {
                            if (this.userAssignedOrgs[userId].length > 0) {
                                assignments.push({
                                    user_id: userId,
                                    org_ids: this.userAssignedOrgs[userId].map(org => org.id)
                                });
                            }
                        });

                        if (assignments.length === 0) {
                            this.$emitter.emit('add-flash', { type: 'warning', message: 'No assignments to save.' });
                            return;
                        }

                        this.isAssigning = true;
                        this.$axios.post("{{ route('admin.mass_assign.assign') }}", {
                            assignments: assignments
                        })
                        .then(response => {
                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                            this.selectedOrgs = [];
                            this.fetchEntities();
                            Object.keys(this.userAssignedOrgs).forEach(key => {
                                this.userAssignedOrgs[key] = [];
                            });
                        })
                        .catch(error => {
                            this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || 'Assignment failed.' });
                        })
                        .finally(() => {
                            this.isAssigning = false;
                        });
                    },

                    distributeEvenly() {
                        if (this.organizations.length === 0) {
                            this.$emitter.emit('add-flash', { type: 'warning', message: 'No organizations to distribute.' });
                            return;
                        }

                        if (this.selectedUsers.length === 0) {
                            this.$emitter.emit('add-flash', { type: 'warning', message: 'Please select at least one user.' });
                            return;
                        }

                        if (confirm(`Are you sure you want to distribute ${this.organizations.length} organizations evenly among ${this.selectedUsers.length} users?`)) {
                            this.isAssigning = true;
                            const orgIds = this.organizations.map(org => org.id);
                            
                            this.$axios.post("{{ route('admin.mass_assign.assign') }}", {
                                organization_ids: orgIds,
                                user_ids: this.selectedUsers
                            })
                            .then(response => {
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                this.selectedOrgs = [];
                                this.fetchEntities();
                                Object.keys(this.userAssignedOrgs).forEach(key => {
                                    this.userAssignedOrgs[key] = [];
                                });
                            })
                            .catch(error => {
                                this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || 'Assignment failed.' });
                            })
                            .finally(() => {
                                this.isAssigning = false;
                            });
                        }
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
