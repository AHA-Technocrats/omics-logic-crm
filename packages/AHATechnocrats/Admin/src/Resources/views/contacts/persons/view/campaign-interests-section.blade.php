{!! view_render_event('admin.contact.persons.view.campaign-interests.before', ['person' => $person]) !!}

@include('admin::contacts.persons.view.campaign-interests')

<div id="person-leads" class="rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
        <h3 class="flex items-center gap-2 font-semibold dark:text-white">
            <span class="icon-product text-xl"></span>
            @lang('admin::app.contacts.persons.view.portal.interested-title')
        </h3>
    </div>

    <v-person-campaign-interests
        list-endpoint="{{ route('admin.contacts.persons.campaigns.index', $person->id) }}"
        detail-endpoint="{{ route('admin.contacts.persons.campaigns.show', ['id' => $person->id, 'leadId' => '__lead__']) }}"
    ></v-person-campaign-interests>
</div>

{!! view_render_event('admin.contact.persons.view.campaign-interests.after', ['person' => $person]) !!}
