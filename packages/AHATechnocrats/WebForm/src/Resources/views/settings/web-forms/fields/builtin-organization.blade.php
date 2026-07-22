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
                
                <input
                    type="hidden"
                    name="persons[organization_country]"
                    :value="selectedOrganizationCountry"
                />
                
                <input
                    type="hidden"
                    name="persons[organization_type]"
                    :value="selectedOrganizationType"
                />
                
                <input
                    type="hidden"
                    name="persons[organization_website]"
                    :value="selectedOrganizationWebsite"
                />

                <ul
                    v-if="showOrganizationSuggestions"
                    class="absolute z-20 mt-1 max-h-64 w-full overflow-y-auto rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
                >
                    <li
                        v-if="organizationSuggestions.length === 0"
                        class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400"
                    >
                        No results found.
                    </li>
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
                    <li
                        class="cursor-pointer border-t border-gray-200 px-3 py-2 text-center text-sm font-medium text-blue-600 hover:bg-gray-50 dark:border-gray-700 dark:text-blue-400 dark:hover:bg-gray-800"
                        @mousedown.prevent="openManualOrgModal"
                    >
                        Not found? Add University
                    </li>
                </ul>
            </div>

            <x-web_form::form.control-group.error control-name="persons[organization_name]" />
        </x-web_form::form.control-group>

        <!-- Manual Organization Modal -->
        <div v-if="showManualOrgModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; background-color: rgba(0,0,0,0.5); padding: 16px;">
            <div style="width: 100%; max-width: 450px; border-radius: 8px; background-color: #fff; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); text-align: left; box-sizing: border-box;">
                <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 600; color: #111827; font-family: inherit;">Add University / Organization</h3>
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 4px; font-size: 14px; font-weight: 500; color: #374151; font-family: inherit;">Name <span style="color: #ef4444;">*</span></label>
                    <input type="text" v-model="manualOrgName" style="width: 100%; border-radius: 6px; border: 1px solid #d1d5db; padding: 10px 12px; font-size: 14px; outline: none; box-sizing: border-box; font-family: inherit; color: #111827;" placeholder="University Name" />
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 4px; font-size: 14px; font-weight: 500; color: #374151; font-family: inherit;">Website <span style="font-weight: 400; color: #6b7280;">(Optional)</span></label>
                    <input type="url" v-model="manualOrgWebsite" style="width: 100%; border-radius: 6px; border: 1px solid #d1d5db; padding: 10px 12px; font-size: 14px; outline: none; box-sizing: border-box; font-family: inherit; color: #111827;" placeholder="https://..." />
                </div>
                
                <div style="margin-bottom: 24px; display: flex; gap: 12px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 4px; font-size: 14px; font-weight: 500; color: #374151; font-family: inherit;">Country <span style="color: #ef4444;">*</span></label>
                        <select v-model="manualOrgCountry" style="width: 100%; border-radius: 6px; border: 1px solid #d1d5db; padding: 10px 12px; font-size: 14px; outline: none; box-sizing: border-box; font-family: inherit; color: #111827; background-color: #fff;">
                            <option value="">Select country</option>
                            @foreach (config('omicslogic.countries', []) as $country)
                                <option value="{{ $country }}">{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 4px; font-size: 14px; font-weight: 500; color: #374151; font-family: inherit;">Type <span style="color: #ef4444;">*</span></label>
                        <select v-model="manualOrgType" style="width: 100%; border-radius: 6px; border: 1px solid #d1d5db; padding: 10px 12px; font-size: 14px; outline: none; box-sizing: border-box; font-family: inherit; color: #111827; background-color: #fff;">
                            <option value="">Select type</option>
                            @foreach (\AHATechnocrats\OmicsLogic\Enums\OrganizationType::cases() as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" @click="closeManualOrgModal" style="border-radius: 6px; background-color: #f3f4f6; border: 1px solid #d1d5db; padding: 8px 16px; font-size: 14px; font-weight: 500; color: #374151; cursor: pointer; font-family: inherit; transition: background-color 0.2s;">Cancel</button>
                    <button type="button" @click="saveManualOrg" style="border-radius: 6px; background-color: #2563eb; border: none; padding: 8px 16px; font-size: 14px; font-weight: 500; color: #fff; cursor: pointer; font-family: inherit; transition: background-color 0.2s;">Save</button>
                </div>
            </div>
        </div>
    </div>
@endif
