<v-modal-cascade-delete ref="cascadeDeleteModal"></v-modal-cascade-delete>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-modal-cascade-delete-template"
    >
        <Teleport to="body">
            <div v-if="isOpen">
                <transition
                    tag="div"
                    name="modal-overlay"
                    enter-class="duration-300 ease-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-class="duration-200 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        class="fixed inset-0 z-[10003] bg-gray-500 bg-opacity-50 transition-opacity"
                        v-show="isOpen"
                        @click="close"
                    ></div>
                </transition>

                <transition
                    tag="div"
                    name="modal-content"
                    enter-class="duration-300 ease-out"
                    enter-from-class="translate-y-4 opacity-0 md:translate-y-0 md:scale-95"
                    enter-to-class="translate-y-0 opacity-100 md:scale-100"
                    leave-class="duration-200 ease-in"
                    leave-from-class="translate-y-0 opacity-100 md:scale-100"
                    leave-to-class="translate-y-4 opacity-0 md:translate-y-0 md:scale-95"
                >
                    <div
                        class="fixed inset-0 z-[10004] transform overflow-y-auto transition"
                        v-if="isOpen"
                    >
                        <div class="flex min-h-full items-center justify-center p-4">
                            <div
                                class="box-shadow w-full max-w-[400px] overflow-hidden rounded-xl bg-white dark:bg-gray-900 max-md:max-w-[92vw]"
                                @click.stop
                            >
                                <!-- Header -->
                                <div class="relative border-b px-4 py-3 dark:border-gray-800">
                                    <p class="pr-7 text-sm font-bold text-gray-800 dark:text-white">
                                        @lang('omicslogic::app.delete-timeline.title')
                                    </p>
                                    <p
                                        v-if="timeline?.entity?.name"
                                        class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        @{{ timeline.entity.name }}
                                    </p>
                                    <button
                                        type="button"
                                        class="icon-cross-large absolute right-2 top-2 rounded-md p-1 text-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
                                        @click="close"
                                    ></button>
                                </div>

                                <div class="px-4 py-3">
                                    <p
                                        v-if="isLoading"
                                        class="py-6 text-center text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        @lang('omicslogic::app.delete-timeline.loading')
                                    </p>

                                    <template v-else-if="timeline">
                                        <!-- Progress bar -->
                                        <div class="mb-4">
                                            <div class="mb-2 flex items-center justify-between text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                                <span>@{{ progressLabel }}</span>
                                                <span>@{{ progressPercent }}%</span>
                                            </div>
                                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                <div
                                                    class="h-full rounded-full bg-brandColor transition-all duration-500 ease-out"
                                                    :style="{ width: progressPercent + '%' }"
                                                ></div>
                                            </div>
                                        </div>

                                        <!-- Horizontal stepper -->
                                        <div class="mb-4 flex items-center">
                                            <template
                                                v-for="(step, stepIndex) in timeline.steps"
                                                :key="'step-' + step.key"
                                            >
                                                <div class="flex shrink-0 flex-col items-center">
                                                    <div
                                                        class="flex h-7 w-7 items-center justify-center rounded-full text-[10px] font-bold"
                                                        :class="stepDotClass(step.status)"
                                                    >
                                                        <span v-if="step.status === 'complete'">✓</span>
                                                        <span v-else>@{{ stepIndex + 1 }}</span>
                                                    </div>
                                                    <span
                                                        class="mt-1 max-w-[4rem] truncate text-center text-[10px] leading-tight"
                                                        :class="stepLabelClass(step.status)"
                                                    >
                                                        @{{ step.label }}
                                                    </span>
                                                </div>

                                                <div
                                                    v-if="stepIndex < timeline.steps.length - 1"
                                                    class="mx-1 mb-4 h-0.5 min-w-[1rem] flex-1 rounded-full bg-gray-200 dark:bg-gray-700"
                                                    :class="step.status === 'complete' ? '!bg-brandColor' : ''"
                                                ></div>
                                            </template>
                                        </div>

                                        <!-- Active step panel only -->
                                        <div
                                            v-if="activeStep"
                                            class="rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-950"
                                        >
                                            <div class="border-b border-gray-200 px-3 py-2 dark:border-gray-800">
                                                <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                                                    @lang('omicslogic::app.delete-timeline.current-step')
                                                </p>
                                                <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                                    @{{ activeStep.label }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    @{{ activeStep.description }}
                                                </p>
                                            </div>

                                            <div class="p-3">
                                                <div
                                                    v-if="! activeStep.items.length"
                                                    class="py-3 text-center text-xs text-gray-500 dark:text-gray-400"
                                                >
                                                    @lang('omicslogic::app.delete-timeline.empty-step')
                                                </div>

                                                <div
                                                    v-for="item in activeStep.items"
                                                    :key="`${activeStep.key}-${item.type}-${item.id}`"
                                                    class="mb-2 flex items-center justify-between gap-2 rounded-md border border-gray-200 bg-white px-2.5 py-2 last:mb-0 dark:border-gray-700 dark:bg-gray-900"
                                                >
                                                    <p class="min-w-0 flex-1 truncate text-xs font-medium text-gray-800 dark:text-white">
                                                        @{{ item.label }}
                                                    </p>
                                                    <div class="flex shrink-0 items-center gap-1.5">
                                                        <a
                                                            v-if="item.view_url"
                                                            :href="item.view_url"
                                                            target="_blank"
                                                            class="text-[11px] font-semibold text-brandColor hover:underline"
                                                        >
                                                            @lang('omicslogic::app.delete-timeline.view-item')
                                                        </a>
                                                        <button
                                                            v-if="item.blocked"
                                                            type="button"
                                                            class="text-[11px] text-gray-400"
                                                            disabled
                                                        >
                                                            @lang('omicslogic::app.delete-timeline.blocked-item')
                                                        </button>
                                                        <button
                                                            v-else-if="canDeleteItem(activeStep, item)"
                                                            type="button"
                                                            class="secondary-button !px-2 !py-0.5 !text-[11px]"
                                                            :disabled="deletingKey === deleteKey(activeStep, item)"
                                                            @click="deleteItem(activeStep, item)"
                                                        >
                                                            @lang('omicslogic::app.delete-timeline.delete-item')
                                                        </button>
                                                    </div>
                                                </div>

                                                <div
                                                    v-if="timeline.can_delete && isFinalStep(activeStep)"
                                                    class="mt-3 flex justify-end border-t border-gray-200 pt-3 dark:border-gray-800"
                                                >
                                                    <button
                                                        type="button"
                                                        class="primary-button !px-3 !py-1 !text-xs"
                                                        :disabled="isDeletingFinal"
                                                        @click="deleteFinal"
                                                    >
                                                        @lang('omicslogic::app.delete-timeline.delete-final')
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex justify-end border-t px-4 py-2.5 dark:border-gray-800">
                                    <button
                                        type="button"
                                        class="transparent-button !px-3 !py-1 !text-xs"
                                        @click="close"
                                    >
                                        @lang('omicslogic::app.delete-timeline.close-btn')
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
        </Teleport>
    </script>

    <script type="module">
        app.component('v-modal-cascade-delete', {
            template: '#v-modal-cascade-delete-template',

            data() {
                return {
                    isOpen: false,
                    previewUrl: null,
                    timeline: null,
                    isLoading: false,
                    deletingKey: null,
                    isDeletingFinal: false,
                    progressLabelTemplate: @json(trans('omicslogic::app.delete-timeline.progress-label')),
                };
            },

            computed: {
                activeStep() {
                    if (! this.timeline?.steps?.length) {
                        return null;
                    }

                    return this.timeline.steps.find(step => step.status === 'active')
                        ?? this.timeline.steps[this.timeline.steps.length - 1];
                },

                completedStepCount() {
                    if (! this.timeline?.steps) {
                        return 0;
                    }

                    return this.timeline.steps.filter(step => step.status === 'complete').length;
                },

                progressPercent() {
                    if (! this.timeline?.steps?.length) {
                        return 0;
                    }

                    const total = this.timeline.steps.length;
                    let completed = this.completedStepCount;

                    if (this.timeline.can_delete && this.activeStep && this.isFinalStep(this.activeStep)) {
                        completed = total - 1;
                    }

                    return Math.min(100, Math.round((completed / total) * 100));
                },

                progressLabel() {
                    const total = this.timeline?.steps?.length ?? 0;

                    return this.progressLabelTemplate
                        .replace(':completed', this.completedStepCount)
                        .replace(':total', total);
                },
            },

            created() {
                this.$emitter.on('open-cascade-delete-modal', this.open);
            },

            beforeUnmount() {
                this.$emitter.off('open-cascade-delete-modal', this.open);
            },

            methods: {
                open({ previewUrl }) {
                    this.previewUrl = previewUrl;
                    this.isOpen = true;
                    document.body.style.overflow = 'hidden';
                    this.loadTimeline();
                },

                close() {
                    this.isOpen = false;
                    document.body.style.overflow = 'auto';
                    this.timeline = null;
                    this.previewUrl = null;
                },

                loadTimeline() {
                    if (! this.previewUrl) {
                        return;
                    }

                    this.isLoading = true;

                    this.$axios.get(this.previewUrl)
                        .then(response => {
                            this.timeline = response.data;
                        })
                        .catch(error => {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || error.message,
                            });

                            this.close();
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
                },

                deleteKey(step, item) {
                    return `${step.key}-${item.type}-${item.id}`;
                },

                canDeleteItem(step, item) {
                    if (step.status === 'pending') {
                        return false;
                    }

                    if (item.type === 'lead') {
                        return step.status === 'active';
                    }

                    if (item.type === 'person') {
                        return step.status === 'active' && ! item.blocked;
                    }

                    return false;
                },

                isFinalStep(step) {
                    if (! this.timeline?.steps?.length) {
                        return false;
                    }

                    return this.timeline.steps[this.timeline.steps.length - 1]?.key === step.key;
                },

                stepDotClass(status) {
                    return {
                        complete: 'bg-green-500 text-white',
                        active: 'bg-brandColor text-white',
                        pending: 'bg-gray-200 text-gray-400 dark:bg-gray-700 dark:text-gray-500',
                    }[status] || 'bg-gray-200 text-gray-400';
                },

                stepLabelClass(status) {
                    return {
                        complete: 'text-green-600 dark:text-green-400',
                        active: 'text-brandColor font-semibold',
                        pending: 'text-gray-400',
                    }[status] || 'text-gray-400';
                },

                deleteItem(step, item) {
                    this.deletingKey = this.deleteKey(step, item);

                    this.$axios.delete(item.delete_url)
                        .then(response => {
                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: response.data.message,
                            });

                            this.loadTimeline();
                            this.$emitter.emit('refresh-datagrid');
                        })
                        .catch(error => {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || error.message,
                            });
                        })
                        .finally(() => {
                            this.deletingKey = null;
                        });
                },

                deleteFinal() {
                    if (! this.timeline?.can_delete || ! this.timeline?.delete_url) {
                        return;
                    }

                    this.isDeletingFinal = true;

                    this.$axios.delete(this.timeline.delete_url)
                        .then(response => {
                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: response.data.message,
                            });

                            this.$emitter.emit('refresh-datagrid');
                            this.close();
                        })
                        .catch(error => {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message,
                            });
                        })
                        .finally(() => {
                            this.isDeletingFinal = false;
                        });
                },
            },
        });
    </script>
@endPushOnce
