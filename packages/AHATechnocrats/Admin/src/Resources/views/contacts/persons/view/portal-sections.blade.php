<!-- CRM Activities -->
<x-admin::activities
    :endpoint="route('admin.contacts.persons.activities.index', $person->id)"
    active-type="all"
/>

<!-- Portal profile + activity (live Firebase) -->
@include('admin::contacts.persons.view.portal-panel-section')

<!-- Interested Campaigns (lazy-loaded leads) -->
@include('admin::contacts.persons.view.campaign-interests-section')
