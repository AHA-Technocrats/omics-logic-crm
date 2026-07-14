@php
    $mode = $mode ?? 'create';
    $langPrefix = $mode === 'edit' ? 'admin::app.settings.webforms.edit' : 'admin::app.settings.webforms.create';
@endphp

<div class="mb-6 overflow-x-auto border-b border-gray-200 pb-4 dark:border-gray-800">
    <ol class="flex min-w-max items-center gap-2 sm:gap-3">
        <li
            v-for="step in wizardSteps"
            :key="step.id"
            class="flex items-center gap-2 sm:gap-3"
        >
            <button
                type="button"
                class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-left transition-colors"
                :class="currentStep === step.id
                    ? 'bg-brandColor/10 text-brandColor'
                    : (currentStep > step.id ? 'text-gray-700 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500')"
                @click="goToStep(step.id)"
            >
                <span
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 text-sm font-semibold"
                    :class="currentStep === step.id
                        ? 'border-brandColor bg-brandColor text-white'
                        : (currentStep > step.id
                            ? 'border-brandColor bg-brandColor/10 text-brandColor'
                            : 'border-gray-300 bg-white text-gray-400 dark:border-gray-700 dark:bg-gray-900')"
                >
                    <template v-if="currentStep > step.id">✓</template>
                    <template v-else>@{{ step.id }}</template>
                </span>

                <span class="hidden text-sm font-medium sm:inline">
                    @{{ step.label }}
                </span>
            </button>

            <span
                v-if="step.id < wizardSteps.length"
                class="hidden h-px w-6 bg-gray-300 sm:block dark:bg-gray-700"
                :class="{ '!bg-brandColor': currentStep > step.id }"
            ></span>
        </li>
    </ol>
</div>
