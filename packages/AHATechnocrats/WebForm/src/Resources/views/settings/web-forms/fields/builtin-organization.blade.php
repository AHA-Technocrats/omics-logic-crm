@if ($webForm->organization_field && $webForm->organization_field !== 'none')
    @php
        $isRequired = $webForm->organization_field === 'required';
    @endphp

    <div class="webform-field">
        <x-web_form::form.control-group>
            <x-web_form::form.control-group.label
                for="persons[organization_name]"
                class="{{ $isRequired ? 'required' : '' }}"
                style="color: {{ $webForm->attribute_label_color }} !important;"
            >
                Company / Organization / University
            </x-web_form::form.control-group.label>

            <div class="relative">
                <v-field
                    v-slot="{ field, errors }"
                    name="persons[organization_name]"
                    rules="{{ $isRequired ? 'required' : '' }}"
                    label="Company / Organization / University"
                >
                    <input
                        type="text"
                        name="persons[organization_name]"
                        id="persons[organization_name]"
                        v-bind="field"
                        :class="[errors.length ? 'border !border-red-600 hover:border-red-600' : '']"
                        class="w-full rounded border border-gray-300 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        placeholder="Type your university, school, or institute name"
                        autocomplete="off"
                        @input="onOrganizationInput($event, field)"
                        @focus="onOrganizationInput($event, field)"
                        @blur="hideOrganizationSuggestions"
                    />
                </v-field>

                <input
                    type="hidden"
                    name="persons[organization_id]"
                    :value="selectedOrganizationId"
                />

                <ul
                    v-if="showOrganizationSuggestions && organizationSuggestions.length"
                    class="absolute z-20 mt-1 max-h-56 w-full overflow-y-auto rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
                >
                    <li
                        v-for="organization in organizationSuggestions"
                        :key="organization.id"
                        class="cursor-pointer px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800"
                        @mousedown.prevent="selectOrganization(organization)"
                    >
                        @{{ organization.name }}
                        <span
                            v-if="organization.country_code"
                            class="ml-1 text-xs text-gray-500 dark:text-gray-400"
                        >
                            (@{{ organization.country_code }})
                        </span>
                    </li>
                </ul>
            </div>

            <x-web_form::form.control-group.error control-name="persons[organization_name]" />
        </x-web_form::form.control-group>
    </div>
@endif
