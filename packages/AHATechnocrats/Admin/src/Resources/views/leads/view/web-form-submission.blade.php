@php
    $submission = $webFormSubmission ?? null;
    $rows = app(\AHATechnocrats\OmicsLogic\Services\WebFormSubmissionPresenter::class)
        ->presentForLead($submission, $lead->person);
@endphp

{!! view_render_event('admin.leads.view.web-form-submission.before', ['lead' => $lead, 'webFormSubmission' => $submission]) !!}

@if ($submission || count($rows) > 0)
    <div class="flex w-full flex-col gap-4 border-b border-gray-300 p-4 dark:border-gray-800">
        <x-admin::accordion class="select-none !border-none">
            <x-slot:header class="!p-0">
                <div class="flex w-full items-center justify-between gap-4 font-semibold dark:text-white">
                    <h4>@lang('omicslogic::app.fields.web-form-submission')</h4>
                </div>
            </x-slot>

            <x-slot:content class="mt-4 !px-0 !pb-0">
                <dl class="grid grid-cols-1 gap-3 text-sm">
                    @if ($submission?->webForm)
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.fields.web-form')</dt>
                            <dd class="text-right font-medium dark:text-white">
                                <a
                                    href="{{ route('admin.web_forms.responses.index', $submission->web_form_id) }}"
                                    class="text-brandColor hover:underline"
                                    target="_blank"
                                >
                                    {{ $submission->webForm->title }}
                                </a>
                            </dd>
                        </div>
                    @endif

                    @foreach ($rows as $row)
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-600 dark:text-gray-300">{{ $row['label'] }}</dt>
                            <dd class="max-w-[60%] whitespace-pre-line text-right font-medium dark:text-white">{{ $row['value'] }}</dd>
                        </div>
                    @endforeach

                    @if ($submission)
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-600 dark:text-gray-300">@lang('omicslogic::app.fields.submitted-at')</dt>
                            <dd class="text-right font-medium dark:text-white">
                                {{ $submission->created_at?->format('D M d, Y H:i A') ?? '—' }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </x-slot>
        </x-admin::accordion>
    </div>
@endif

{!! view_render_event('admin.leads.view.web-form-submission.after', ['lead' => $lead, 'webFormSubmission' => $submission]) !!}
