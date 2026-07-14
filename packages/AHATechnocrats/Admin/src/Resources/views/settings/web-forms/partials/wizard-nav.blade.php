@php
    $mode = $mode ?? 'create';
    $langPrefix = $mode === 'edit' ? 'admin::app.settings.webforms.edit' : 'admin::app.settings.webforms.create';
@endphp

<div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-4 dark:border-gray-800">
    <button
        type="button"
        class="secondary-button"
        v-show="currentStep > 1"
        @click="prevStep"
    >
        @lang($langPrefix.'.back-btn')
    </button>

    <div class="ml-auto flex items-center gap-2.5">
        <button
            type="button"
            class="primary-button"
            v-show="currentStep < wizardSteps.length"
            @click="nextStep"
        >
            @lang($langPrefix.'.next-btn')
        </button>

        <button
            type="submit"
            class="primary-button"
            v-show="currentStep === wizardSteps.length"
        >
            @lang($langPrefix.'.save-btn')
        </button>
    </div>
</div>
