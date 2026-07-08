{!! view_render_event('admin.contact.persons.view.portal-panel.before', ['person' => $person]) !!}

<div class="rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900">
    <v-person-portal-panel
        person-name="{{ strip_tags($person->name) }}"
        person-email="{{ $person->emails[0]['value'] ?? '' }}"
    ></v-person-portal-panel>
</div>

{!! view_render_event('admin.contact.persons.view.portal-panel.after', ['person' => $person]) !!}
