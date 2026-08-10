@props(['person', 'size' => 'sm'])

@php
    use AHATechnocrats\OmicsLogic\Support\LeadScoreBadge;

    $breakdown = LeadScoreBadge::breakdownFromPerson($person);
    $badgeClass = $size === 'xs'
        ? 'inline-flex cursor-pointer items-center rounded-full px-2 py-0.5 text-[10px] font-semibold leading-none'
        : 'inline-flex cursor-pointer items-center rounded-full px-2 py-0.5 text-xs font-semibold';
@endphp

<div
    class="relative inline-flex"
    data-lead-score-tip
>
    <button
        type="button"
        class="{{ $badgeClass }} {{ $breakdown['class'] }}"
        data-lead-score-tip-trigger
        aria-expanded="false"
        aria-label="@lang('omicslogic::app.fields.score-breakdown')"
    >
        {{ $breakdown['total'] }} · {{ $breakdown['label'] }}
    </button>

    <div
        class="absolute right-0 top-full z-[9999] mt-1 hidden w-[200px] max-w-[calc(100vw-16px)] rounded-md border border-gray-200 bg-white p-2.5 text-left shadow-2xl dark:border-gray-700 dark:bg-gray-900"
        data-lead-score-tip-panel
        role="tooltip"
    >
        <div class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            @lang('omicslogic::app.fields.score-breakdown')
        </div>

        @foreach ($breakdown['factors'] as $factor)
            <div class="flex items-center justify-between gap-2 py-0.5 text-[11px] text-gray-700 dark:text-gray-200">
                <span class="min-w-0 truncate pr-2">{{ $factor['label'] }}</span>
                <span class="shrink-0 whitespace-nowrap font-medium tabular-nums">{{ $factor['points'] }}/{{ $factor['max'] }}</span>
            </div>
        @endforeach

        <div class="mt-1.5 flex items-center justify-between gap-2 border-t border-gray-200 pt-1.5 text-[11px] font-semibold dark:border-gray-700 dark:text-white">
            <span>@lang('omicslogic::app.fields.score-tip.total')</span>
            <span class="shrink-0 whitespace-nowrap tabular-nums">{{ $breakdown['total'] }} · {{ $breakdown['label'] }}</span>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (() => {
                if (window.__leadScoreTipBound) {
                    return;
                }

                window.__leadScoreTipBound = true;

                const setOpen = (root, open) => {
                    const panel = root.querySelector('[data-lead-score-tip-panel]');
                    const trigger = root.querySelector('[data-lead-score-tip-trigger]');

                    if (! panel) {
                        return;
                    }

                    panel.classList.toggle('hidden', ! open);
                    panel.classList.toggle('flex', open);
                    panel.classList.toggle('flex-col', open);

                    if (trigger) {
                        trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                    }
                };

                const sync = (root) => {
                    const open = root.dataset.pinned === 'true' || root.dataset.hovered === 'true';

                    setOpen(root, open);
                };

                const unpinAll = (except = null) => {
                    document.querySelectorAll('[data-lead-score-tip]').forEach((root) => {
                        if (root === except) {
                            return;
                        }

                        root.dataset.pinned = 'false';
                        sync(root);
                    });
                };

                document.addEventListener('mouseover', (event) => {
                    const root = event.target.closest('[data-lead-score-tip]');

                    if (! root || root.dataset.hovered === 'true') {
                        return;
                    }

                    root.dataset.hovered = 'true';
                    sync(root);
                });

                document.addEventListener('mouseout', (event) => {
                    const root = event.target.closest('[data-lead-score-tip]');

                    if (! root) {
                        return;
                    }

                    const next = event.relatedTarget;

                    if (next && root.contains(next)) {
                        return;
                    }

                    root.dataset.hovered = 'false';
                    sync(root);
                });

                document.addEventListener('click', (event) => {
                    const trigger = event.target.closest('[data-lead-score-tip-trigger]');

                    if (trigger) {
                        event.preventDefault();
                        event.stopPropagation();

                        const root = trigger.closest('[data-lead-score-tip]');

                        if (! root) {
                            return;
                        }

                        const willPin = root.dataset.pinned !== 'true';

                        unpinAll(willPin ? root : null);
                        root.dataset.pinned = willPin ? 'true' : 'false';
                        sync(root);

                        return;
                    }

                    if (! event.target.closest('[data-lead-score-tip]')) {
                        unpinAll();
                    }
                });
            })();
        </script>
    @endpush
@endonce
