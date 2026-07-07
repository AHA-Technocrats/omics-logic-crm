@php
    $availablePrograms = $availablePrograms ?? \AHATechnocrats\WebForm\Helpers\WebFormPrograms::all();
    $storedOptions = old('program_options', $webForm->program_options ?? null);

    if (is_string($storedOptions)) {
        $storedOptions = json_decode($storedOptions, true);
    }

    if (! isset($allProgramsSelected)) {
        $allProgramsSelected = empty($storedOptions);
    }

    if (! isset($selectedProgramKeys)) {
        $selectedProgramKeys = $allProgramsSelected
            ? array_column($availablePrograms, 'key')
            : array_values($storedOptions ?? []);
    }
@endphp

<!-- Program Field Option -->
<x-admin::form.control-group>
    <x-admin::form.control-group.label class="required">
        Interested in Program Field
    </x-admin::form.control-group.label>

    <x-admin::form.control-group.control
        type="select"
        name="program_field"
        id="program_field"
        value="optional"
        label="Interested in Program Field"
        v-model="programField"
    >
        <option value="none">None (Hidden)</option>
        <option value="optional">Optional</option>
        <option value="required">Required</option>
    </x-admin::form.control-group.control>

    <x-admin::form.control-group.error control-name="program_field"/>
</x-admin::form.control-group>

<div
    class="mb-4 rounded-lg border border-gray-200 p-4 dark:border-gray-800"
    v-if="programField !== 'none'"
>
    <div class="mb-3 flex flex-col gap-1">
        <p class="text-sm font-semibold text-gray-800 dark:text-white">
            Programs to show on form
        </p>

        <p class="text-xs text-gray-500 dark:text-gray-400">
            Choose which predefined programs appear on the public form, or select all.
        </p>
    </div>

    <label class="mb-3 flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-800 dark:text-white">
        <input
            type="checkbox"
            class="peer hidden"
            v-model="allProgramsSelected"
            @change="onAllProgramsToggle"
        />
        <span class="icon-checkbox-outline peer-checked:icon-checkbox-select cursor-pointer rounded-md text-2xl peer-checked:text-brandColor"></span>
        Select all programs
    </label>

    <div class="flex flex-col gap-2">
        <label
            v-for="program in availablePrograms"
            :key="program.key"
            class="flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-300"
        >
            <input
                type="checkbox"
                class="peer hidden"
                :value="program.key"
                v-model="selectedProgramKeys"
                :disabled="allProgramsSelected"
                @change="onProgramOptionChange"
            />
            <span
                class="icon-checkbox-outline peer-checked:icon-checkbox-select rounded-md text-2xl peer-checked:text-brandColor"
                :class="{ 'opacity-50': allProgramsSelected }"
            ></span>
            @{{ program.name }}
        </label>
    </div>

    <input
        type="hidden"
        name="program_options"
        :value="programOptionsJson"
    />
</div>
