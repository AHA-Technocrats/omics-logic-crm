{!! view_render_event('admin.contacts.organizations.view.scenario.before', ['organization' => $organization]) !!}

<div class="rounded-lg bg-gray-900 p-4 text-sm leading-relaxed text-white dark:bg-gray-950">
    <p>
        <span class="font-bold">@lang('omicslogic::app.organizations.view.scenario-label')</span>
        @lang('omicslogic::app.organizations.view.scenario-text', ['name' => $organization->name])
    </p>
</div>

{!! view_render_event('admin.contacts.organizations.view.scenario.after', ['organization' => $organization]) !!}
