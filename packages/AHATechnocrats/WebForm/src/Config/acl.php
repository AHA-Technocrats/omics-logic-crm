<?php

return [
    [
        'key' => 'web_forms',
        'name' => 'web_form::app.acl.title',
        'route' => ['admin.web_forms.index', 'admin.web_forms.responses.index', 'admin.web_forms.responses.export'],
        'sort' => 5,
    ], [
        'key' => 'web_forms.view',
        'name' => 'web_form::app.acl.view',
        'route' => 'admin.settings.web_forms.view',
        'sort' => 1,
    ], [
        'key' => 'web_forms.create',
        'name' => 'web_form::app.acl.create',
        'route' => ['admin.web_forms.create', 'admin.web_forms.store'],
        'sort' => 2,
    ], [
        'key' => 'web_forms.edit',
        'name' => 'web_form::app.acl.edit',
        'route' => ['admin.web_forms.edit', 'admin.web_forms.update', 'admin.web_forms.customization.update'],
        'sort' => 3,
    ], [
        'key' => 'web_forms.delete',
        'name' => 'web_form::app.acl.delete',
        'route' => 'admin.web_forms.delete',
        'sort' => 4,
    ],
];
