<?php

namespace AHATechnocrats\WebForm\DataGrids;

use AHATechnocrats\DataGrid\DataGrid;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class WebFormDataGrid extends DataGrid
{
    protected $sortColumn = 'created_at';

    protected $sortOrder = 'desc';

    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('web_forms')
            ->leftJoin('web_form_submissions', 'web_forms.id', '=', 'web_form_submissions.web_form_id')
            ->leftJoin('email_templates', 'web_forms.email_template_id', '=', 'email_templates.id')
            ->addSelect(
                'web_forms.id',
                'web_forms.title',
                'web_forms.is_active',
                'web_forms.create_lead',
                'web_forms.send_submitter_email',
                'web_forms.created_at',
                'email_templates.name as email_template_name',
            )
            ->selectRaw('COUNT(web_form_submissions.id) as submissions_count')
            ->selectRaw('MAX(web_form_submissions.created_at) as last_submission_at')
            ->groupBy(
                'web_forms.id',
                'web_forms.title',
                'web_forms.is_active',
                'web_forms.create_lead',
                'web_forms.send_submitter_email',
                'web_forms.created_at',
                'email_templates.name',
            );

        $this->addFilter('id', 'web_forms.id');
        $this->addFilter('is_active', 'web_forms.is_active');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.settings.webforms.index.datagrid.id'),
            'type' => 'string',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'title',
            'label' => trans('admin::app.settings.webforms.index.datagrid.title'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'is_active',
            'label' => trans('admin::app.settings.webforms.index.datagrid.status'),
            'type' => 'boolean',
            'sortable' => true,
            'filterable' => true,
            'closure' => fn ($row) => $row->is_active
                ? trans('admin::app.settings.webforms.index.datagrid.active')
                : trans('admin::app.settings.webforms.index.datagrid.inactive'),
        ]);

        $this->addColumn([
            'index' => 'submissions_count',
            'label' => trans('admin::app.settings.webforms.index.datagrid.submissions'),
            'type' => 'integer',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'last_submission_at',
            'label' => trans('admin::app.settings.webforms.index.datagrid.last-submission'),
            'type' => 'datetime',
            'sortable' => true,
            'closure' => fn ($row) => $row->last_submission_at
                ? core()->formatDate($row->last_submission_at, 'd M Y H:i A')
                : '—',
        ]);

        $this->addColumn([
            'index' => 'create_lead',
            'label' => trans('admin::app.settings.webforms.index.datagrid.create-lead'),
            'type' => 'boolean',
            'closure' => fn ($row) => $row->create_lead
                ? trans('admin::app.settings.webforms.index.datagrid.yes')
                : trans('admin::app.settings.webforms.index.datagrid.no'),
        ]);

        $this->addColumn([
            'index' => 'email_template_name',
            'label' => trans('admin::app.settings.webforms.index.datagrid.email-template'),
            'type' => 'string',
            'closure' => fn ($row) => $row->email_template_name ?: '—',
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.settings.webforms.index.datagrid.created-at'),
            'type' => 'datetime',
            'sortable' => true,
            'closure' => fn ($row) => core()->formatDate($row->created_at, 'd M Y'),
        ]);
    }

    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('web_forms.view')) {
            $this->addAction([
                'index' => 'responses',
                'icon' => 'icon-stats-up',
                'title' => trans('admin::app.settings.webforms.index.datagrid.responses'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.web_forms.responses.index', $row->id),
            ]);

        }

        if (bouncer()->hasPermission('web_forms.edit')) {
            $this->addAction([
                'index' => 'edit',
                'icon' => 'icon-edit',
                'title' => trans('admin::app.settings.webforms.index.datagrid.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.web_forms.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('web_forms.delete')) {
            $this->addAction([
                'index' => 'delete',
                'icon' => 'icon-delete',
                'title' => trans('admin::app.settings.webforms.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.web_forms.delete', $row->id),
            ]);
        }
    }
}
