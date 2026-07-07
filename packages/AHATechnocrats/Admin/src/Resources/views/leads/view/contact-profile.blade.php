@php
    $person = $lead->person;
    $canEdit = bouncer()->hasPermission('leads.edit') && bouncer()->hasPermission('persons.edit');
@endphp

{!! view_render_event('admin.leads.view.contact-profile.before', ['lead' => $lead]) !!}

@if ($person)
    <div class="flex w-full flex-col gap-4 border-b border-gray-300 p-4 dark:border-gray-800">
        <x-admin::accordion class="select-none !border-none">
            <x-slot:header class="!p-0">
                <div class="flex w-full items-center justify-between gap-4 font-semibold dark:text-white">
                    <h4>@lang('omicslogic::app.fields.contact-profile')</h4>

                    @if ($canEdit)
                        <a
                            href="{{ route('admin.contacts.persons.edit', $person->id) }}"
                            class="icon-edit rounded-md p-1.5 text-2xl transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
                            target="_blank"
                        ></a>
                    @endif
                </div>
            </x-slot>

            <x-slot:content class="mt-4 !px-0 !pb-0">
                @include('admin::omics.partials.crm-profile-inline', [
                    'person' => $person,
                    'allowEdit' => $canEdit,
                    'updateUrl' => route('admin.leads.person-profile.update', $lead->id),
                ])
            </x-slot>
        </x-admin::accordion>
    </div>
@endif

{!! view_render_event('admin.leads.view.contact-profile.after', ['lead' => $lead]) !!}
