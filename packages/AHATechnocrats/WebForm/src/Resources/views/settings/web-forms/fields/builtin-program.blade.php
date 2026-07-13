@php
    use AHATechnocrats\WebForm\Helpers\WebFormPrograms;

    $campaigns = WebFormPrograms::forForm($webForm);
    $isRequired = ($webForm->program_field ?? 'required') === 'required';
    $programOptions = collect($campaigns);
@endphp

<div class="webform-field">
    <x-web_form::form.control-group>
        <x-web_form::form.control-group.label
            class="{{ $isRequired ? 'required' : '' }}"
            style="color: {{ $webForm->attribute_label_color }} !important;"
        >
            Interested in Campaign
        </x-web_form::form.control-group.label>

        <div style="display: flex !important; flex-direction: column !important; gap: 12px !important; margin-top: 8px !important; width: 100% !important;">
            @foreach ($programOptions as $program)
                <v-field
                    v-slot="{ field }"
                    type="checkbox"
                    name="persons[program_interest][]"
                    value="{{ $program['name'] }}"
                    rules="{{ $isRequired ? 'required' : '' }}"
                >
                    <label style="display: flex !important; align-items: center !important; justify-content: flex-start !important; gap: 12px !important; margin: 0 !important; padding: 0 !important; width: 100% !important; cursor: pointer !important; text-align: left !important; float: none !important;">
                        <input
                            type="checkbox"
                            v-bind="field"
                            value="{{ $program['name'] }}"
                            style="margin: 0 !important; width: 20px !important; height: 20px !important; flex-shrink: 0 !important; cursor: pointer !important;"
                            class="rounded-sm border-2 border-gray-400 bg-white text-gray-700 focus:ring-0 checked:border-gray-700 checked:bg-gray-700 dark:border-gray-500 dark:bg-gray-800"
                        />
                        <span style="font-size: 15px !important; font-weight: 400 !important; margin: 0 !important; padding: 0 !important; text-align: left !important; width: auto !important; display: block !important;" class="text-gray-800 dark:text-gray-200">
                            {{ $program['name'] }}
                        </span>
                    </label>
                </v-field>
            @endforeach

            <!-- Other Option -->
            <v-field
                v-slot="{ field }"
                type="checkbox"
                name="persons[program_interest][]"
                value="__other__"
            >
                <label style="display: flex !important; align-items: center !important; justify-content: flex-start !important; gap: 12px !important; margin: 0 !important; padding: 0 !important; width: 100% !important; cursor: pointer !important; text-align: left !important; float: none !important;">
                    <input
                        type="checkbox"
                        v-bind="field"
                        value="__other__"
                        style="margin: 0 !important; width: 20px !important; height: 20px !important; flex-shrink: 0 !important; cursor: pointer !important;"
                        class="rounded-sm border-2 border-gray-400 bg-white text-gray-700 focus:ring-0 checked:border-gray-700 checked:bg-gray-700 dark:border-gray-500 dark:bg-gray-800"
                    />
                    <span style="font-size: 15px !important; font-weight: 400 !important; margin: 0 !important; padding: 0 !important; text-align: left !important; width: auto !important; display: block !important; white-space: nowrap !important;" class="text-gray-800 dark:text-gray-200">
                        Other:
                    </span>
                    <input 
                        type="text" 
                        name="persons[program_other]" 
                        class="text-[15px] text-gray-800 focus:border-gray-500 focus:ring-0 dark:border-gray-600 dark:text-gray-200" 
                        style="margin-left: 4px !important; width: 100% !important; border: none !important; border-bottom: 1px solid #d1d5db !important; background: transparent !important; padding: 2px 4px !important; box-shadow: none !important;"
                        placeholder=""
                    />
                </label>
            </v-field>
        </div>

        <x-web_form::form.control-group.error control-name="persons[program_interest][]" />
    </x-web_form::form.control-group>
</div>
